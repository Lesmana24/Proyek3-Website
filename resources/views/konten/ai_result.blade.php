@extends('layouts.main')

@section('title', $title ?? 'Hasil Diagnosa')
@push('page-styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Radio+Canada:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        /* Agar margin/padding container full width dari layout utama (bila menggunakan Bootstrap di layout) */
        .container { min-width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        a { text-decoration: none !important; }
        /* Kustomisasi scrollbar untuk kesan elegan */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; margin-block: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
@endpush

@section('content')
<div x-data="{ chatOpen: false }" class="bg-gray-50 font-['Radio_Canada'] min-h-screen py-6 px-4 flex justify-center items-center relative">
    
    {{-- Main Container --}}
    <div class="w-full max-w-6xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row md:h-[90vh]">
        
        {{-- Kiri: Visual Pane (Hanya Gambar & Tombol Back) --}}
        <div class="w-full md:w-2/5 bg-gray-50 relative flex-shrink-0">
            
            {{-- Tombol Back --}}
            <div class="absolute top-4 left-4 z-10">
                @if(isset($isPreview) && $isPreview)
                    <form action="{{ route('ai.reset') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 bg-white/90 backdrop-blur-sm shadow-md rounded-full flex items-center justify-center hover:bg-gray-100 transition group text-gray-700">
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('ai.index') }}" class="w-10 h-10 bg-white/90 backdrop-blur-sm shadow-md rounded-full flex items-center justify-center hover:bg-gray-100 transition group text-gray-700 inline-flex">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Foto Tanaman Full Cover --}}
            <img 
                src="{{ asset('storage/' . $scanResult->image_path) }}" 
                alt="Foto Tanaman" 
                class="w-full h-64 md:h-full object-cover rounded-t-2xl md:rounded-none"
            >
        </div>

        {{-- Kanan: Data & Scrollable Pane --}}
        <div class="w-full md:w-3/5 p-6 md:p-8 lg:p-10 overflow-y-auto custom-scrollbar flex flex-col bg-white">
            
            {{-- Header dengan Tombol Chat --}}
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-extrabold text-[#4C732E]">Hasil Diagnosis AI</h1>
                
                {{-- Tombol Chat AI Botanist --}}
                <button 
                    @click="chatOpen = true" 
                    class="flex items-center gap-2 px-4 py-2 border-2 border-[#4C732E] text-[#4C732E] font-bold rounded-xl hover:bg-[#4C732E] hover:text-white transition-all transform hover:scale-105 shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                    <span class="hidden sm:inline">Tanya AI</span>
                </button>
            </div>
            
            {{-- Section 1: Identitas Tanaman (Grid 2 Kolom) --}}
            <div class="mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2">Identitas Tanaman</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Kolom 1 --}}
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                        <span class="block text-xs font-semibold text-gray-400 tracking-wider uppercase mb-1">Nama Tanaman</span>
                        <div class="font-bold text-gray-900 text-xl capitalize mb-2 line-clamp-1">
                            {{ $scanResult->plant_name ?? 'Tanaman Tidak Diketahui' }}
                        </div>
                        <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md uppercase tracking-wider font-bold text-xs shadow-sm">
                            Status: {{ $scanResult->ai_health_status ?? '-' }}
                        </span>
                    </div>
                    
                    {{-- Kolom 2 --}}
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex flex-col justify-between">
                        <div class="mb-3">
                            <span class="block text-xs font-semibold text-gray-400 tracking-wider uppercase mb-1">Penyakit Utama</span>
                            @if(strtolower($scanResult->ai_health_status) === 'healthy' || empty($scanResult->disease_name))
                                <span class="font-bold text-green-600 text-lg leading-tight block">Kondisi Sehat</span>
                            @else
                                <span class="font-bold text-red-600 text-lg leading-tight block line-clamp-2" title="{{ $scanResult->disease_name }}">
                                    {{ $scanResult->disease_name }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 tracking-wider uppercase mb-1">Akurasi Deteksi</span>
                            <span class="font-extrabold text-[#4C732E] text-xl">{{ number_format($scanResult->confidence_score, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Panduan Perawatan --}}
            <div class="mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2">Panduan Perawatan</h2>
                <div class="space-y-3">
                    {{-- Card Cahaya --}}
                    <div class="flex items-start bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-2xl mr-3 mt-0.5">☀️</span>
                        <div>
                            <span class="block text-xs font-bold text-[#4C732E] uppercase tracking-wider mb-1">Cahaya</span>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $scanResult->care_light ?? 'Informasi tidak tersedia' }}</p>
                        </div>
                    </div>
                    {{-- Card Penyiraman --}}
                    <div class="flex items-start bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-2xl mr-3 mt-0.5">💧</span>
                        <div>
                            <span class="block text-xs font-bold text-[#4C732E] uppercase tracking-wider mb-1">Penyiraman</span>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $scanResult->care_water ?? 'Informasi tidak tersedia' }}</p>
                        </div>
                    </div>
                    {{-- Card Suhu --}}
                    <div class="flex items-start bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-2xl mr-3 mt-0.5">🌡️</span>
                        <div>
                            <span class="block text-xs font-bold text-[#4C732E] uppercase tracking-wider mb-1">Suhu Ideal</span>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $scanResult->care_temperature ?? 'Informasi tidak tersedia' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Masalah & Gejala --}}
            <div class="mb-8 bg-orange-50/70 border border-orange-100 rounded-xl p-5">
                <h2 class="text-lg font-bold text-orange-800 mb-3 flex items-center">
                    <span class="mr-2 text-xl">⚠️</span> Masalah & Gejala
                </h2>
                @if(!empty($scanResult->problems_list) && is_array($scanResult->problems_list) && count($scanResult->problems_list) > 0)
                    <ul class="space-y-2 list-none">
                        @foreach($scanResult->problems_list as $problem)
                            <li class="flex items-start text-sm text-orange-900 leading-relaxed">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-orange-400 mt-1.5 mr-2.5 flex-shrink-0"></span>
                                {{ $problem }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-orange-700 italic text-sm">Tidak ada masalah spesifik yang ditemukan.</p>
                @endif
            </div>

            {{-- Section 4: Action Buttons --}}
            <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                @if(isset($isPreview) && $isPreview)
                    <form action="{{ route('ai.store') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-[#4C732E] hover:bg-[#3b5924] text-white py-3.5 px-4 rounded-xl font-bold transition flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Simpan Laporan
                        </button>
                    </form>
                    <form action="{{ route('ai.reset') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-white hover:bg-gray-50 text-[#4C732E] border-2 border-[#4C732E] py-3.5 px-4 rounded-xl font-bold transition flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Scan Ulang
                        </button>
                    </form>
                @else
                    <a href="{{ route('ai.index') }}" class="w-full bg-white hover:bg-gray-50 text-[#4C732E] border-2 border-[#4C732E] py-3.5 px-4 rounded-xl font-bold transition flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Scan Ulang
                    </a>
                @endif
            </div>

        </div>
    </div>

    {{-- Chat UI (Slide-over Drawer) --}}
    <div x-cloak>
        {{-- Background Overlay Gelap Transparan --}}
        <div 
            x-show="chatOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm"
            @click="chatOpen = false"
        ></div>

        {{-- Slide-over Panel Kanan --}}
        <div 
            class="fixed inset-y-0 right-0 w-full md:w-[450px] bg-white shadow-2xl transform transition-transform duration-300 ease-in-out z-50 flex flex-col"
            :class="chatOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            {{-- Header Panel Chat --}}
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#4C732E]/10 flex items-center justify-center border border-[#4C732E]/20">
                        <span class="text-2xl">🤖</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg leading-tight">Konsultasi AI</h3>
                        <div class="flex items-center mt-1">
                            <span class="w-2.5 h-2.5 bg-green-500 rounded-full mr-2 shadow-sm"></span>
                            <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Online (Botanist)</span>
                        </div>
                    </div>
                </div>
                <button @click="chatOpen = false" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2.5 rounded-full transition duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Area Pesan (Scrollable) --}}
            <div id="chat-box" class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar bg-white">
                
                {{-- Tanggal --}}
                <div class="text-center">
                    <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-100">Hari ini</span>
                </div>

                {{-- AI Welcome Message --}}
                <div class="flex justify-start">
                    <div class="bg-gray-50 border border-gray-200 text-gray-800 px-5 py-3.5 rounded-2xl rounded-tl-sm max-w-[85%] shadow-sm">
                        <p class="text-sm font-medium leading-relaxed">Halo! Saya AI Botanist. Ada yang bisa saya bantu terkait kondisi tanaman {{ $scanResult->plant_name ?? 'ini' }} Anda?</p>
                        <span class="text-[10px] text-gray-400 mt-2 block font-bold tracking-wider">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
                {{-- Chat bubbles render from old history --}}
                @if(isset($scanResult->id) && isset($scanResult->chatHistories))
                    @foreach($scanResult->chatHistories as $chat)
                        @if($chat->sender === 'user')
                            <div class="flex justify-end">
                                <div class="bg-[#4C732E] text-white px-5 py-3.5 rounded-2xl rounded-tr-sm max-w-[85%] shadow-md">
                                    <p class="text-sm font-medium leading-relaxed">{{ $chat->message }}</p>
                                    <span class="text-[10px] text-green-200 mt-2 block text-right font-bold tracking-wider">{{ $chat->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start ai-history-bubble" data-message="{{ htmlspecialchars($chat->message) }}" data-time="{{ $chat->created_at->format('H:i') }}">
                            </div>
                        @endif
                    @endforeach
                @endif
                
                {{-- Chat bubbles akan dirender ke sini via JS --}}
            </div>

            {{-- Area Input Fix Bawah --}}
            <div class="p-5 border-t border-gray-100 bg-white flex-shrink-0 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                <div class="flex gap-3 items-end">
                    <textarea 
                        id="chat-input"
                        rows="1" 
                        placeholder="Tanya soal tanamanmu..." 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4C732E]/30 focus:border-[#4C732E] resize-none custom-scrollbar font-medium"
                    ></textarea>
                    <button type="button" id="send-btn" class="bg-[#4C732E] text-white p-3.5 rounded-xl hover:bg-[#3b5924] transition shadow-md flex-shrink-0 group">
                        <svg id="send-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 group-hover:scale-110 transition-transform">
                            <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                        </svg>
                        <svg id="loading-icon" class="w-5 h-5 hidden animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');
        const chatBox = document.getElementById('chat-box');
        const sendIcon = document.getElementById('send-icon');
        const loadingIcon = document.getElementById('loading-icon');
        
        const plantName = @json($scanResult->plant_name ?? '');
        const diseaseName = @json($scanResult->disease_name ?? '');
        const scanId = @json($scanResult->id ?? null);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Fungsi untuk format jam
        function getCurrentTime() {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        }

        // Fungsi Escape HTML untuk mencegah XSS
        function escapeHTML(str) {
            return str.replace(/[&<>'"]/g, function(tag) {
                const charsToReplace = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                };
                return charsToReplace[tag] || tag;
            });
        }

        // Fungsi Parsing Markdown Dasar
        function parseBasicMarkdown(text) {
            let html = escapeHTML(text);
            // Replace **bold**
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-gray-900">$1</strong>');
            // Replace *italic*
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            // Bersihkan bullet point markdown sisa jika masih ada di awal baris
            html = html.replace(/^[\*\-]\s/gm, '• ');
            // Ganti line breaks dengan <br>
            html = html.replace(/(?:\r\n|\r|\n)/g, '<br>');
            return html;
        }

        // Parse and render old AI histories
        document.querySelectorAll('.ai-history-bubble').forEach(el => {
            const rawMsg = el.getAttribute('data-message');
            const time = el.getAttribute('data-time');
            const parsed = parseBasicMarkdown(rawMsg);
            el.innerHTML = `
                <div class="bg-gray-50 border border-gray-200 text-gray-800 px-5 py-3.5 rounded-2xl rounded-tl-sm max-w-[85%] shadow-sm">
                    <p class="text-sm font-medium leading-relaxed">${parsed}</p>
                    <span class="text-[10px] text-gray-400 mt-2 block font-bold tracking-wider">${time}</span>
                </div>
            `;
            el.classList.remove('ai-history-bubble');
        });

        // Render User Bubble
        function renderUserBubble(text) {
            const time = getCurrentTime();
            const safeText = escapeHTML(text);
            const html = `
                <div class="flex justify-end">
                    <div class="bg-[#4C732E] text-white px-5 py-3.5 rounded-2xl rounded-tr-sm max-w-[85%] shadow-md">
                        <p class="text-sm font-medium leading-relaxed">${safeText}</p>
                        <span class="text-[10px] text-green-200 mt-2 block text-right font-bold tracking-wider">${time}</span>
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', html);
            scrollToBottom();
        }

        // Render AI Bubble
        function renderAIBubble(text) {
            const time = getCurrentTime();
            const formattedText = parseBasicMarkdown(text);
            const html = `
                <div class="flex justify-start">
                    <div class="bg-gray-50 border border-gray-200 text-gray-800 px-5 py-3.5 rounded-2xl rounded-tl-sm max-w-[85%] shadow-sm">
                        <p class="text-sm font-medium leading-relaxed">${formattedText}</p>
                        <span class="text-[10px] text-gray-400 mt-2 block font-bold tracking-wider">${time}</span>
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', html);
            scrollToBottom();
        }

        // Auto-scroll ke bawah saat ada pesan baru
        function scrollToBottom() {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth'
            });
        }

        // Menangani input Enter untuk mengirim
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener('click', function() {
            sendMessage();
        });

        function sendMessage() {
            const message = chatInput.value.trim();
            if (message === '') return;

            // 1. Tampilkan pesan user
            renderUserBubble(message);
            chatInput.value = '';
            
            // 2. Set status "loading"
            chatInput.setAttribute('disabled', 'true');
            sendBtn.setAttribute('disabled', 'true');
            sendIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');

            // Render temporary bubble loading ("Mengetik...")
            const loadingId = 'loading-' + Date.now();
            const loadingHtml = `
                <div id="${loadingId}" class="flex justify-start">
                    <div class="bg-gray-50 border border-gray-200 text-gray-800 px-5 py-3.5 rounded-2xl rounded-tl-sm shadow-sm flex items-center gap-2">
                        <p class="text-sm font-medium italic text-gray-500">AI sedang memikirkan jawaban...</p>
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', loadingHtml);
            scrollToBottom();

            // 3. Kirim via Fetch ke controller
            fetch("{{ route('ai.chat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    plant_name: plantName,
                    disease: diseaseName,
                    scan_id: scanId
                })
            })
            .then(response => response.json())
            .then(data => {
                // Hapus loading bubble
                document.getElementById(loadingId).remove();
                
                // Tampilkan pesan AI
                if (data.reply) {
                    renderAIBubble(data.reply);
                } else {
                    renderAIBubble("Maaf, terjadi kesalahan saat mengambil balasan.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById(loadingId).remove();
                renderAIBubble("Terjadi gangguan koneksi ke server AI.");
            })
            .finally(() => {
                // 4. Kembalikan state aktif
                chatInput.removeAttribute('disabled');
                sendBtn.removeAttribute('disabled');
                sendIcon.classList.remove('hidden');
                loadingIcon.classList.add('hidden');
                chatInput.focus();
            });
        }
    });
</script>

@endsection
