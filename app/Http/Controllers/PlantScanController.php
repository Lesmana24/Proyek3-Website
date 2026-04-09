<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlantHealthScan;
use App\Models\ChatHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PlantScanController extends Controller
{
    /**
     * Menampilkan halaman awal dan mem-passing riwayat scan
     */
    public function index()
    {
        $historyScans = PlantHealthScan::where('user_id', Auth::guard('pengguna')->id())
            ->latest()
            ->get();

        return view('konten.ai', compact('historyScans'));
    }

    public function upload(Request $request)
    {
        // 1. Validasi input gambar (maksimal 5MB)
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Simpan gambar SEMENTARA ke dalam disk public direktori 'temp'
            $path = $file->storeAs('temp', $filename, 'public');

            try {
                // JEMBATAN API: Tembak foto ke server Python FastAPI
                $response = Http::attach(
                    'file', file_get_contents($file), $file->getClientOriginalName()
                )->post('http://127.0.0.1:8001/diagnosa');

                if ($response->successful()) {
                    $hasil_ai = $response->json();
                    
                    $nama_tanaman = $hasil_ai['nama_tanaman'] ?? 'Tanaman Tidak Diketahui';
                    $nama_penyakit = $hasil_ai['penyakit'] ?? null;
                    $akurasi = $hasil_ai['akurasi'] ?? 0;

                    // Logic simpel: Kalau nama penyakitnya ada kata 'healthy', 'sehat', 'tidak ada', atau null/kosong, berarti sehat.
                    $penyakit_lower = strtolower($nama_penyakit);
                    if (empty($nama_penyakit) || 
                        str_contains($penyakit_lower, 'healthy') || 
                        str_contains($penyakit_lower, 'sehat') || 
                        str_contains($penyakit_lower, 'tidak ada')
                    ) {
                        $status_kesehatan = 'healthy';
                    } else {
                        $status_kesehatan = 'infected';
                    }

                    // Integrasi ke Groq API (Llama 3) untuk Panduan Perawatan secara sekuensial
                    $groqData = $this->getPlantCareFromGroq($nama_tanaman, $nama_penyakit);

                    // Simpan state hasil ini ke dalam session SEMENTARA
                    session(['temp_ai_scan' => [
                        'image_path' => $path,
                        'ai_health_status' => $status_kesehatan,
                        'plant_name' => $nama_tanaman,
                        'disease_name' => $nama_penyakit,
                        'confidence_score' => $akurasi,
                        'care_light' => $groqData['care_light'] ?? 'Informasi belum tersedia.',
                        'care_water' => $groqData['care_water'] ?? 'Informasi belum tersedia.',
                        'care_temperature' => $groqData['care_temperature'] ?? 'Informasi belum tersedia.',
                        'problems_list' => $groqData['problems_list'] ?? [],
                    ]]);

                    // Kembalikan response sukses ke frontend (AJAX/Fetch)
                    return response()->json([
                        'message' => 'Analisis AI selesai! Memuat pratinjau...',
                        'status' => 'success',
                        'redirect_url' => route('ai.preview')
                    ]);
                } else {
                    Storage::disk('public')->delete($path);
                    return response()->json([
                        'message' => 'Gagal mendapatkan respon dari AI.',
                        'status' => 'error'
                    ], 500);
                }

            } catch (\Exception $e) {
                Storage::disk('public')->delete($path);
                return response()->json([
                    'message' => 'Server AI sedang offline. Pastikan uvicorn sudah menyala!',
                    'status' => 'error'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Gambar gagal diunggah.',
            'status' => 'error'
        ], 400);
    }

    /**
     * Menampilkan halaman PREVIEW hasil diagnosis AI sebelum disimpan
     */
    public function preview()
    {
        $tempScan = session('temp_ai_scan');
        
        if (!$tempScan) {
            return redirect()->route('ai.index')->with('error', 'Tidak ada data scan sementara yang tersedia.');
        }

        // Convert array to object
        $scanResult = (object) $tempScan;
        $isPreview = true;

        return view('konten.ai_result', compact('scanResult', 'isPreview'));
    }

    /**
     * Memindahkan temp file dan insert permanen ke database
     */
    public function storeReport(Request $request)
    {
        $tempScan = session('temp_ai_scan');

        if (!$tempScan) {
            return redirect()->route('ai.index')->with('error', 'Sesi telah berakhir atau tidak valid.');
        }

        $oldPath = $tempScan['image_path'];
        $filename = basename($oldPath);
        $newPath = 'plant_scans/' . $filename;

        // Pindahkan physical file di storage public
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->move($oldPath, $newPath);
        }

        $userId = Auth::guard('pengguna')->check() ? Auth::guard('pengguna')->id() : null;

        // Insert database permanen
        $scan = PlantHealthScan::create([
            'user_id' => $userId,
            'image_path' => $newPath,
            'ai_health_status' => $tempScan['ai_health_status'],
            'disease_name' => $tempScan['disease_name'],
            'confidence_score' => $tempScan['confidence_score'],
            'plant_name' => $tempScan['plant_name'],
            'care_light' => $tempScan['care_light'] ?? null,
            'care_water' => $tempScan['care_water'] ?? null,
            'care_temperature' => $tempScan['care_temperature'] ?? null,
            'problems_list' => $tempScan['problems_list'] ?? null,
        ]);

        // Bersihkan session
        session()->forget('temp_ai_scan');

        return redirect()->route('ai.result', $scan->id)->with('success', 'Laporan berhasil disimpan!');
    }

    /**
     * Menghapus temporary data (Scan Ulang)
     */
    public function reset(Request $request)
    {
        $tempScan = session('temp_ai_scan');
        
        if ($tempScan && isset($tempScan['image_path'])) {
            Storage::disk('public')->delete($tempScan['image_path']);
        }
        
        session()->forget('temp_ai_scan');

        return redirect()->route('ai.index');
    }

    /**
     * Menampilkan halaman hasil diagnosis AI yang sudah di-save
     */
    public function result($id)
    {
        $scanResult = PlantHealthScan::with('chatHistories')->findOrFail($id);

        // Proteksi sederhana agar user hanya bisa melihat scannya sendiri jika login
        if ($scanResult->user_id && Auth::guard('pengguna')->check() && Auth::guard('pengguna')->id() !== $scanResult->user_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $isPreview = false;

        return view('konten.ai_result', compact('scanResult', 'isPreview'));
    }

    /**
     * Memanggil Groq API (Llama 3) untuk generate panduan perawatan (JSON Mode)
     */
    private function getPlantCareFromGroq($plantName, $diseaseName)
    {
        $apiKey = env('GROQ_API_KEY');
        if (!$apiKey) {
            \Illuminate\Support\Facades\Log::error('GROQ_API_KEY tidak terkonfigurasi di file .env');
            return [];
        }

        $kondisi = empty($diseaseName) || strtolower($diseaseName) == 'healthy' ? 'Sehat / Tidak Berpenyakit' : $diseaseName;

        $prompt = "Bertindaklah sebagai ahli botani. Berikan panduan perawatan untuk tanaman [{$plantName}] dengan kondisi/penyakit: [{$kondisi}].
WAJIB balas HANYA dengan response JSON murni tanpa markdown backticks. Gunakan struktur JSON persis seperti ini:
{
  \"care_light\": \"deskripsi pencahayaan singkat\",
  \"care_water\": \"deskripsi penyiraman singkat\",
  \"care_temperature\": \"deskripsi suhu ideal singkat\",
  \"problems_list\": [\"gejala 1\", \"gejala 2\", \"solusi 1\"]
}";

        try {
            $response = Http::withoutVerifying()
                ->retry(1, 1000)
                ->withToken($apiKey) // Inject Token via Header Bearer Auth
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://api.groq.com/openai/v1/chat/completions", [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'user', // Kita masukkan langsung as User prompt
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'response_format' => ['type' => 'json_object'] // Flag Groq JSON Mode
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // 1. Ekstraksi String dari format respon Groq (OpenAI style)
                $jsonString = $result['choices'][0]['message']['content'] ?? '{}';
                
                // 2. BERSIHKAN Markdown jika Llama3 ngotot nge-generate backticks
                $jsonString = preg_replace('/^```(?:json)?\s*/i', '', $jsonString);
                $jsonString = preg_replace('/\s*```$/i', '', $jsonString);
                $jsonString = trim($jsonString);
                
                // 3. Parse JSON menjadi Associative Array
                $decoded = json_decode($jsonString, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Illuminate\Support\Facades\Log::error("Groq JSON Parse Error: " . json_last_error_msg() . " | Raw Response: " . $jsonString);
                    return [];
                }
                
                return $decoded;
            } else {
                \Illuminate\Support\Facades\Log::error("Groq Response Failed (Status " . $response->status() . "): " . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Groq Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Memproses chat dari user ke Groq API (Llama 3) via AJAX
     */
    public function chatBotanist(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'plant_name' => 'required|string',
            'disease' => 'nullable|string'
        ]);

        $apiKey = env('GROQ_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Maaf, API Key Groq tidak terkonfigurasi di server (.env).'], 500);
        }

        $plantName = $request->input('plant_name');
        $disease = $request->input('disease');
        $kondisi = empty($disease) || strtolower($disease) == 'healthy' ? 'Sehat / Tidak Berpenyakit' : $disease;
        $userMessage = $request->input('message');
        $scanId = $request->input('scan_id');

        // Simpan pesan user ke database jika scan_id ada
        if ($scanId) {
            ChatHistory::create([
                'plant_health_scan_id' => $scanId,
                'sender' => 'user',
                'message' => $userMessage
            ]);
        }

        $systemPrompt = "ROLE: Anda adalah Pakar Agronomi dan Ahli Patologi Tumbuhan profesional.
CONTEXT: User saat ini sedang melihat hasil diagnosa tanaman [{$plantName}] yang terindikasi [{$kondisi}].
TONE & STYLE: Gunakan bahasa Indonesia yang baku, profesional, dan to-the-point. Jawaban harus berbasis sains, objektif, dan langsung memberikan informasi atau solusi praktis.
NEGATIVE CONSTRAINTS (LARANGAN KERAS): DILARANG KERAS menggunakan kata seru/basa-basi (seperti 'Wah', 'Hmm', 'Aduh'). DILARANG memberikan simpati emosional (seperti 'Saya mengerti kebingungan Anda' atau 'Sayang sekali tanaman Anda sakit'). DILARANG menggunakan gaya bahasa kiasan atau hiperbola. Langsung jawab intinya saja.
FORMATTING: Gunakan paragraf pendek. Anda boleh mem-bold (**teks**) kata kunci ilmiah atau bahan aktif untuk penekanan.
STRICT GUARDRAILS: Kewajiban Mutlak: Anda HANYA diizinkan menjawab pertanyaan seputar tanaman, pertanian, botani, hama, penyakit, dan cara perawatannya. Jika user bertanya topik DI LUAR itu (seperti matematika, teknologi, politik, cuaca, hiburan, dll), ANDA WAJIB MENOLAK UNTUK MENJAWAB. Jangan memberikan solusi atau menebak jawaban. Langsung balas dengan kalimat sopan seperti: 'Mohon maaf, saya adalah pakar agronomi. Saya hanya bisa membantu menjawab pertanyaan seputar perawatan tanaman dan patologi tumbuhan.'";

        try {
            $response = Http::withoutVerifying()
                ->retry(3, 1000) // Coba ulang 3 kali dengan jeda 1 detik jika gagal
                ->withToken($apiKey) // Menggunakan Bearer Token (Groq format)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://api.groq.com/openai/v1/chat/completions", [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => "Pertanyaan: " . $userMessage
                        ]
                    ],
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                // Parsing response bergaya OpenAI/Groq
                $reply = $response->json('choices.0.message.content') ?? 'Maaf, AI tidak memberikan balasan.';
                
                // Simpan balasan AI ke database jika scan_id ada
                if ($scanId) {
                    ChatHistory::create([
                        'plant_health_scan_id' => $scanId,
                        'sender' => 'ai',
                        'message' => trim($reply)
                    ]);
                }

                return response()->json([
                    'reply' => trim($reply)
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error("Groq Chat Response Failed: " . $response->body());
                return response()->json(['reply' => 'Maaf, terjadi masalah koneksi dengan AI (Groq API).'], 500);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Groq Chat Exception: " . $e->getMessage());
            return response()->json(['reply' => 'Maaf, server AI sedang sibuk atau offline.'], 500);
        }
    }

    /**
     * Menghapus riwayat scan secara spesifik dari AJAX
     */
    public function destroy($id)
    {
        $scan = PlantHealthScan::where('id', $id)
            ->where('user_id', Auth::guard('pengguna')->id())
            ->first();

        if (!$scan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Riwayat tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        // Hapus fisik gambar di storage
        if (Storage::disk('public')->exists($scan->image_path)) {
            Storage::disk('public')->delete($scan->image_path);
        }

        // Hapus record dari database
        $scan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Riwayat berhasil dihapus.'
        ]);
    }
}