@extends('layouts.main')
@section('title', $title ?? 'Welcome')
@push('page-styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home-welcome.css') }}?v={{ filemtime(public_path('css/home-welcome.css')) }}">
@endpush
@section('content')
<div class="welcome-container">
    <div class="content-left">
        <h1 class="judul">
            <span class="text-gray">Smart</span> <span class="text-green">Plants</span><br>
            <span class="text-green">House</span>
        </h1>
        <div class="button-group">
            <a href="login" class="btn-masuk">Masuk</a>
            <a href="daftar" class="btn-daftar">Daftar</a>
        </div>
    </div>
    
    <div class="content-right">
        <div class="image-wrapper">
            <div class="shape-bg"></div>
            
            <img src="{{ asset('image/pohon.png') }}" alt="Plant Image" class="plant-image">
        </div>
    </div>
</div>
@endsection