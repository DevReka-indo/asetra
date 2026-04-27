@extends('layouts.app')

@section('title', 'Beranda')
@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <h3 class="fw-bold mb-4">Dashboard</h3>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ auth()->user()->firstname }}!</h5>
            <p class="card-text">Ini adalah dashboard untuk staf. Di sini Anda dapat melihat ringkasan informasi penting, notifikasi terbaru, dan akses cepat ke berbagai fitur manajemen aset departemen Anda.</p>
            <a href="{{ route('aset.index') }}" class="btn btn-primary">Lihat Data Aset</a>
        </div>
    </div>
</div>  
@endsection
