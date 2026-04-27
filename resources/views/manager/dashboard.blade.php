@extends('layouts.app')

@section('title', 'Beranda Manager')
@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <h3 class="fw-bold mb-4">Dashboard Manager</h3>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ auth()->user()->firstname }}!</h5>
            <p class="card-text">Ini adalah dashboard khusus untuk peran Manager. Anda dapat memantau seluruh aset dan pengajuan di departemen Anda di sini.</p>
            <a href="{{ route('aset.index') }}" class="btn btn-primary">Lihat Data Aset</a>
        </div>
    </div>
</div>  
@endsection
