@extends('layouts.app')

@section('title', 'Daftar Aset Opname')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-1 py-0 mt-0 page-stock-opname-user-show">
        @include('stock-opname.partials.user-show.header')
        @include('stock-opname.partials.user-show.overview')

        <div class="panel-card">
            <div class="panel-head">
                <ul class="nav nav-tabs-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link tab-danger active"
                            id="pills-belum-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-belum"
                            type="button"
                            role="tab"
                            aria-controls="pills-belum"
                            aria-selected="true"
                        >
                            <i class="fas fa-search"></i>
                            Perlu Dicek
                            <span class="count-badge count-badge-belum">
                                {{ $belumDicek->count() }}
                            </span>
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="pills-ditemukan-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-ditemukan"
                            type="button"
                            role="tab"
                            aria-controls="pills-ditemukan"
                            aria-selected="false"
                        >
                            <i class="fas fa-check-circle"></i>
                            Telah Dicek
                            <span class="count-badge count-badge-telah">
                                {{ $telahDicek->count() }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="panel-body">
                <div class="tab-content" id="pills-tabContent">
                    @include('stock-opname.partials.user-show.unchecked-tab')
                    @include('stock-opname.partials.user-show.checked-tab')
                </div>
            </div>
        </div>
    </div>

    <a
        href="{{ route('aset.scanner', [
            'mode' => 'opname',
            'session_id' => $session->id,
        ]) }}"
        class="floating-scanner-btn d-md-none"
        title="Buka Scanner"
    >
        <i class="fas fa-qrcode"></i>
    </a>

    @include('stock-opname.partials.user-show.manual-check-modal')
@endsection

@push('scripts')
    @include('stock-opname.partials.user-show.scripts')
@endpush
