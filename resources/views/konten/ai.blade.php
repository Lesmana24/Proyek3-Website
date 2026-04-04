@extends('layouts.main')

@section('title', $title ?? 'Cek Kesehatan Tanaman')
@push('page-styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Radio+Canada:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Override bootstrap container khusus halaman ini agar full-width */
        .container { min-width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        a { text-decoration: none !important; }
        /* Pastikan elemen a memiliki warna aslinya saat belum tercover tailwind */
        svg { color: #4C732E; }
    </style>
@endpush

@section('content')
<div x-data="{ historyOpen: false }" class="bg-white font-['Radio_Canada'] min-h-screen flex flex-col items-center justify-center relative px-6 py-12">
    <!-- Top Action Buttons -->
    <div class="absolute top-8 left-0 right-0 px-8 md:px-12 flex justify-between items-center w-full">
        <!-- Back Button -->
        <a href="{{ url('/home') }}" class="w-14 h-14 bg-white shadow-[0_2px_15px_rgba(0,0,0,0.06)] rounded-full flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-6 h-6 text-[#4C732E]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        
        <!-- Right Icon Button -->
        <button @click="historyOpen = true" class="w-14 h-14 bg-white shadow-[0_2px_15px_rgba(0,0,0,0.06)] rounded-full flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-6 h-6 text-[#4C732E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="2" fill="none"></rect>
                <line x1="16" y1="6" x2="16" y2="18" stroke="currentColor" stroke-width="2"></line>
            </svg>
        </button>
    </div>

    {{-- Konten Utama --}}
    <div class="flex flex-col items-center w-full max-w-3xl mt-12 md:mt-2">
        
        {{-- Judul Halaman --}}
        <h1 class="text-[#4C732E] text-5xl md:text-6xl lg:text-7xl font-bold text-center mb-20 md:mb-28 leading-snug">
            Cek Kesehatan <br> Tanaman (AI)
        </h1>

        {{-- Grup Tombol Aksi --}}
        <div id="actionButtons" class="flex flex-col md:flex-row gap-5 w-full justify-center mb-8 transition-all">
            {{-- Hidden Input untuk Files --}}
            <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden">
            <input type="file" id="galleryInput" accept="image/*" class="hidden">

            {{-- Tombol Ambil Foto --}}
            <button type="button" onclick="document.getElementById('cameraInput').click()" class="w-full md:w-auto px-10 py-3.5 bg-[#4C732E] text-white text-lg md:text-xl rounded-full hover:bg-[#3b5924] transition flex items-center justify-center font-semibold">
                Ambil Foto
            </button>
            
            {{-- Tombol Upload --}}
            <button type="button" onclick="document.getElementById('galleryInput').click()" class="w-full md:w-auto px-10 py-3.5 bg-white text-[#717171] text-lg md:text-xl rounded-full border-[2.5px] border-[#4C732E] hover:bg-gray-50 transition flex items-center justify-center font-semibold">
                Upload dari Galeri
            </button>
        </div>

        {{-- Preview Container (Hidden by Info) --}}
        <div id="previewContainer" class="hidden flex-col items-center w-full max-w-sm mb-8 transition-all">
            <img id="previewImage" src="" alt="Preview Gambar" class="w-full aspect-square object-cover rounded-3xl shadow-lg border-[3px] border-[#4C732E] mb-5">
            
            <div class="flex gap-4 w-full justify-center">
                {{-- Tombol Ulangi --}}
                <button type="button" id="btnRetry" class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-100 transition shadow-sm border border-red-200">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
                
                {{-- Tombol Lanjut (Upload) --}}
                <button type="button" id="btnUpload" class="flex-1 px-8 py-4 bg-[#4C732E] text-white text-xl rounded-full hover:bg-[#3b5924] transition flex items-center justify-center font-bold shadow-sm">
                    Lanjut
                    <svg class="w-7 h-7 ml-2" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- UI Status Upload / Loading --}}
        <div id="uploadStatus" class="hidden flex-col items-center mb-6">
            <div id="loadingSpinner" class="w-8 h-8 border-4 border-[#4C732E] border-t-transparent rounded-full animate-spin mb-2"></div>
            <p id="uploadText" class="text-[#4C732E] font-semibold text-center text-sm md:text-base">Mempersiapkan Upload...</p>
        </div>

        {{-- Teks Bantuan --}}
        <p id="helpText" class="text-[#717171] text-sm md:text-base text-center font-medium max-w-md transition-all">
            Unggah foto daun/tanaman dengan pencahayaan cukup
        </p>

    </div>
    
    <!-- History Drawer -->
    <div x-show="historyOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-full"
         class="fixed inset-y-0 right-0 w-full md:w-96 bg-white shadow-2xl z-50 flex flex-col"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-[#4C732E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold text-gray-800">Riwayat Deteksi</h3>
            </div>
            <button @click="historyOpen = false" class="p-2 bg-white rounded-full text-gray-400 hover:text-gray-600 shadow-sm border border-gray-100 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- History List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50 pb-20">
            @forelse($historyScans as $scan)
            <a href="{{ route('ai.result', $scan->id) }}" class="block w-full bg-white border border-gray-100 p-4 rounded-xl shadow-sm hover:shadow-md hover:border-[#4C732E]/30 transition group flex flex-col gap-3">
                <div class="flex items-start gap-4">
                    <img src="{{ Storage::url($scan->image_path) }}" alt="{{ $scan->plant_name }}" class="w-16 h-16 object-cover rounded-lg shadow-sm border border-gray-50">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-800 truncate group-hover:text-[#4C732E] transition">{{ $scan->plant_name }}</h4>
                        @if($scan->ai_health_status == 'healthy')
                            <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Sehat
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                {{ Str::limit($scan->disease_name, 20) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="pt-2 border-t border-gray-50 flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-400">{{ $scan->created_at->diffForHumans() }}</span>
                    <span class="text-xs font-semibold text-[#4C732E] flex items-center group-hover:translate-x-1 transition-transform">
                        Lihat Detail
                        <svg class="w-3.5 h-3.5 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center h-48 text-center px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h4 class="text-gray-800 font-bold mb-1">Belum Ada Riwayat</h4>
                <p class="text-xs text-gray-500">Anda belum pernah melakukan deteksi kesehatan tanaman.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('page-styles')
{{-- JavaScript Handler untuk upload foto AI --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cameraInput = document.getElementById('cameraInput');
        const galleryInput = document.getElementById('galleryInput');
        
        const actionButtons = document.getElementById('actionButtons');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const btnRetry = document.getElementById('btnRetry');
        const btnUpload = document.getElementById('btnUpload');
        const helpText = document.getElementById('helpText');

        const uploadStatus = document.getElementById('uploadStatus');
        const uploadText = document.getElementById('uploadText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        let selectedFile = null;

        function handleFileSelection(event) {
            const file = event.target.files[0];
            if (file) {
                // Validasi ukuran max 5MB di frontend sebelum masuk preview
                if (file.size > 5 * 1024 * 1024) {
                    alert('Oopss! Ukuran fail maksimal adalah 5MB.');
                    event.target.value = '';
                    return;
                }

                selectedFile = file;
                const objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                
                // Transisi UI (sembunyikan tombol awal & teks bantuan, tampilkan preview)
                actionButtons.classList.add('hidden');
                actionButtons.classList.remove('flex');
                
                if (helpText) helpText.classList.add('hidden');
                
                previewContainer.classList.remove('hidden');
                previewContainer.classList.add('flex');
            }
        }

        cameraInput.addEventListener('change', handleFileSelection);
        galleryInput.addEventListener('change', handleFileSelection);

        btnRetry.addEventListener('click', () => {
            // Hapus file dan bersihkan memory URL
            selectedFile = null;
            cameraInput.value = '';
            galleryInput.value = '';
            
            if (previewImage.src) {
                URL.revokeObjectURL(previewImage.src);
                previewImage.src = '';
            }

            // Kembalikan UI state ke awal
            previewContainer.classList.add('hidden');
            previewContainer.classList.remove('flex');
            
            actionButtons.classList.remove('hidden');
            actionButtons.classList.add('flex');
            
            if (helpText) helpText.classList.remove('hidden');
        });

        btnUpload.addEventListener('click', () => {
            if (selectedFile) {
                // Sembunyikan previewContainer supaya bisa fokus ke status loading
                previewContainer.classList.add('hidden');
                previewContainer.classList.remove('flex');

                uploadImage(selectedFile);
            }
        });

        async function uploadImage(file) {
            // Tampilkan loading state
            uploadStatus.classList.remove('hidden');
            uploadStatus.classList.add('flex');
            loadingSpinner.classList.remove('hidden');
            uploadText.innerText = 'Sedang mengupload foto...';
            uploadText.className = 'text-[#4C732E] font-semibold text-center text-sm md:text-base block';

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}'); // CSRF token untuk keamanan form POST Laravel

            try {
                const response = await fetch('{{ route('ai.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (response.ok) {
                    loadingSpinner.classList.add('hidden');
                    uploadText.innerText = result.message || 'Upload berhasil diproses!';
                    // Arahkan ke halaman preview berdasarkan response JSON
                    window.location.href = result.redirect_url || '/ai/result/preview';
                } else {
                    let errorMessage = result.message || 'Upload gagal.';
                    if(result.errors && result.errors.image) {
                        errorMessage = result.errors.image[0];
                    }
                    throw new Error(errorMessage);
                }
            } catch (error) {
                console.error('Upload error:', error);
                loadingSpinner.classList.add('hidden');
                uploadText.innerText = 'Gagal Mengupload: ' + error.message;
                uploadText.className = 'text-red-500 font-semibold text-center text-sm md:text-base mt-2 block';
                
                // Jika gagal, beritahu dan tampilkan UI awal lagi untuk mencoba lagi
                actionButtons.classList.remove('hidden');
                actionButtons.classList.add('flex');
                if (helpText) helpText.classList.remove('hidden');
            } finally {
                // Bersihkan input agar pengguna tidak stuck dengan fail yg sama
                cameraInput.value = '';
                galleryInput.value = '';
                selectedFile = null;
            }
        }
    });
</script>
@endpush
@endsection
