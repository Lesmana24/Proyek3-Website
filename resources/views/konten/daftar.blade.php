@extends('layouts.main')

@section('title', $title ?? 'Daftar')
@push('page-styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home-daftar.css') }}?v={{ filemtime(public_path('css/home-daftar.css')) }}">
@endpush

@section('content')
    <div class="register-layout">
        <!-- Tombol Back Kiri Atas -->
        <a href="/" class="back-btn">
            <svg viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
        </a>

        <!-- Sisi Kiri (Putih) -->
        <div class="bg-left">
            <div class="form-card">
                <h2>Daftar</h2>

                <form action="{{ route('daftar') }}" method="POST">
                    @csrf

                    <!-- Error Messages -->
                    @if($errors->any())
                        <div class="error-msg">
                            @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
                        </div>
                    @endif

                    <div class="input-group">
                        <div class="input-wrapper">
                            <span class="icon-wrapper">
                                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </span>
                            <input type="text" name="nama" class="input-field" required placeholder="Nama">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <span class="icon-wrapper">
                                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                            </span>
                            <input type="password" name="password" class="input-field" required placeholder="Password">
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="btn-submit-wrapper">
                            <button type="submit" class="btn-daftar">Daftar</button>
                        </div>
                        <p class="redirect-login">
                            Sudah punya akun? <a href="login">Masuk</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sisi Kanan (Hijau & Gambar Tanaman) -->
        <div class="bg-right">
            <div class="plant-wrapper">
                <img src="{{ asset('image/pohon2.png') }}" alt="Tanaman Monstera Lebat">
            </div>
        </div>
    </div>
@endsection