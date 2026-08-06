@php
    $totalScope = $belumDicek->count() + $telahDicek->count();
    $progressUser = $totalScope > 0
        ? round(($telahDicek->count() / $totalScope) * 100)
        : 0;

    $circumference = 2 * pi() * 50;
    $offset = $circumference - (($progressUser / 100) * $circumference);
@endphp

<div class="so-detail-hero">
    <div class="so-detail-hero-content row align-items-center g-3">
        <div class="col-md-7">
            <h4 class="fw-bold mt-2 mb-1">
                {{ $isAdmin
                    ? 'Daftar Aset Seluruh Departemen'
                    : 'Daftar Aset di Unit Anda' }}
            </h4>

            <p class="mb-2 opacity-90">
                <i class="far fa-calendar-alt me-1"></i>

                {{ \Carbon\Carbon::parse($session->tanggal_mulai)->format('d M') }}

                <span class="mx-1">→</span>

                {{ \Carbon\Carbon::parse($session->tanggal_berakhir)->format('d M Y') }}
            </p>
        </div>

        <div class="col-md-5">
            <div class="d-flex align-items-center justify-content-md-end gap-3">
                <div class="progress-ring">
                    <svg width="120" height="120">
                        <circle
                            cx="60"
                            cy="60"
                            r="50"
                            class="ring-track"
                        ></circle>

                        <circle
                            cx="60"
                            cy="60"
                            r="50"
                            class="ring-bar hero-progress-circle"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}"
                        ></circle>
                    </svg>

                    <div class="ring-text">
                        <div class="ring-pct hero-progress-percent">
                            {{ $progressUser }}%
                        </div>

                        <div class="ring-sub">Selesai</div>
                    </div>
                </div>

                <div>
                    <div class="opacity-75 small">Progres Anda</div>

                    <h3 class="fw-bold mb-0 text-white">
                        <span class="hero-progress-telah">
                            {{ $telahDicek->count() }}
                        </span>

                        <small style="font-size: .9rem; opacity: .75;">
                            /
                            <span class="hero-progress-total">
                                {{ $totalScope }}
                            </span>
                        </small>
                    </h3>

                    <small class="opacity-75">Aset terdata</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="so-stat-chip d-flex align-items-center gap-3">
            <span class="so-stat-icon primary">
                <i class="fas fa-boxes"></i>
            </span>

            <div>
                <div class="so-stat-lbl">
                    {{ $isAdmin
                        ? 'Total Aset Perusahaan'
                        : 'Total Aset Anda' }}
                </div>

                <div class="so-stat-num so-stat-num-total">
                    {{ $totalScope }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="so-stat-chip d-flex align-items-center gap-3">
            <span class="so-stat-icon danger">
                <i class="fas fa-search"></i>
            </span>

            <div>
                <div class="so-stat-lbl">Perlu Dicek</div>

                <div class="so-stat-num text-danger so-stat-num-belum">
                    {{ $belumDicek->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="so-stat-chip d-flex align-items-center gap-3">
            <span class="so-stat-icon success">
                <i class="fas fa-check-circle"></i>
            </span>

            <div>
                <div class="so-stat-lbl">Telah Dicek</div>

                <div class="so-stat-num text-success so-stat-num-telah">
                    {{ $telahDicek->count() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="scanner-cta d-flex flex-column flex-md-row align-items-md-center gap-3 mb-4">
    <div class="scanner-cta-icon">
        <i class="fas fa-qrcode"></i>
    </div>

    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1 text-dark">Mulai Pindai Aset</h6>

        <p class="mb-0 small text-muted">
            Buka scanner untuk memindai QR code aset, atau gunakan
            "Cek Manual" jika label sulit dipindai.
        </p>
    </div>

    <a
        href="{{ route('aset.scanner', [
            'mode' => 'opname',
            'session_id' => $session->id,
        ]) }}"
        class="scanner-btn d-none d-md-inline-flex align-items-center gap-2"
    >
        <i class="fas fa-camera"></i>
        Buka Scanner
    </a>
</div>
