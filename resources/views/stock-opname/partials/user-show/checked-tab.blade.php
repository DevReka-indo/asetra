<div
    class="tab-pane fade"
    id="pills-ditemukan"
    role="tabpanel"
    aria-labelledby="pills-ditemukan-tab"
    tabindex="0"
>
    @if($telahDicek->isEmpty())
        <div class="empty-row">
            <i class="fas fa-clipboard-list text-muted d-block mb-2"></i>

            <h5 class="fw-bold text-dark mt-2 mb-1">
                Belum Ada Aset yang Dicek
            </h5>

            <p class="text-muted small mb-0">
                Aset yang sudah diperiksa akan muncul pada bagian ini.
            </p>
        </div>
    @else
        <div class="row g-2 align-items-end mb-3">
            <div style="width: 100px;">
                <label
                    for="entriesDitemukan"
                    class="form-label fw-bold small text-muted text-uppercase"
                >
                    Entries
                </label>

                <select
                    id="entriesDitemukan"
                    class="form-select form-select-sm rounded-3 custom-entries-select"
                    data-table="dtDitemukan"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            @if($isAdmin)
                <div class="col-md-3">
                    <label
                        for="divisiFilterDitemukan"
                        class="form-label fw-bold small text-muted text-uppercase"
                    >
                        Divisi
                    </label>

                    <select
                        id="divisiFilterDitemukan"
                        class="form-select form-select-sm rounded-3 custom-divisi-filter"
                        data-table="dtDitemukan"
                    >
                        <option value="">-- Semua Divisi --</option>

                        @foreach($availableDivisis as $divisiName)
                            <option value="{{ $divisiName }}">
                                {{ $divisiName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label
                        for="departmentFilterDitemukan"
                        class="form-label fw-bold small text-muted text-uppercase"
                    >
                        Departemen
                    </label>

                    <select
                        id="departmentFilterDitemukan"
                        class="form-select form-select-sm rounded-3 custom-dept-filter"
                        data-table="dtDitemukan"
                    >
                        <option value="">-- Semua Departemen --</option>

                        @foreach($availableDepts as $deptName)
                            <option value="{{ $deptName }}">
                                {{ $deptName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-3 ms-auto">
                <label
                    for="searchDitemukan"
                    class="form-label fw-bold small text-muted text-uppercase"
                >
                    Pencarian
                </label>

                <div
                    class="input-group input-group-sm input-group-focus rounded-3"
                    style="border: 1px solid #ced4da; background: #fff;"
                >
                    <span class="input-group-text bg-white border-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>

                    <input
                        type="search"
                        id="searchDitemukan"
                        class="form-control border-0 shadow-none bg-transparent custom-search-input"
                        data-table="dtDitemukan"
                        placeholder="Cari aset atau pemeriksa..."
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table mb-0" id="tableDitemukan">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th>Aset</th>
                        <th>Kategori</th>
                        <th>Kondisi Temuan</th>
                        <th>Lokasi Temuan</th>

                        @if($isAdmin)
                            <th class="col-divisi">Divisi</th>
                            <th class="col-dept">Departemen/Unit</th>
                        @endif

                        <th>Foto</th>
                        <th>Dicek Oleh</th>
                        <th>Waktu</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($telahDicek as $detail)
                        @php
                            $aset = $detail->aset;

                            $kondisi = $detail->kondisi_temuan;

                            $kondisiSistem = $aset?->status_kondisi;

                            $kondisiBerubah = filled($kondisi)
                                && filled($kondisiSistem)
                                && $kondisi !== $kondisiSistem;

                            $kondisiBuruk = in_array(
                                $kondisi,
                                [
                                    'Rusak',
                                    'Bongkar',
                                    'Hilang',
                                    'Tidak Teridentifikasi',
                                ],
                                true
                            );

                            $lokasiSistemId = $aset?->lokasi_id;

                            $lokasiTemuanId = $detail->lokasi_temuan;

                            $lokasiBerubah = filled($lokasiTemuanId)
                                && filled($lokasiSistemId)
                                && (string) $lokasiTemuanId !== (string) $lokasiSistemId;

                            $lokasiTemuanNama =
                                $detail->lokasiTemuan?->nama_lokasi
                                ?? $detail->lokasi_temuan_nama
                                ?? '-';
                        @endphp

                        <tr
                            data-divisi="{{ $aset?->resolved_divisi_name ?? '' }}"
                            data-dept="{{ $aset?->resolved_department_name ?? '' }}"
                        >
                            <td class="text-center text-muted">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <div class="aset-cell">
                                    <div class="aset-thumb">
                                        <i class="fas fa-box"></i>
                                    </div>

                                    <div class="aset-info">
                                        <div class="nomor">
                                            {{ $aset?->nomor_aset ?? '-' }}
                                        </div>

                                        <div class="nama">
                                            {{ $aset?->nama_aset ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="text-dark small">
                                    {{ $aset?->kategoriAset?->nama ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if($kondisi === 'Baik')
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Baik
                                    </span>
                                @elseif($kondisi === 'Rusak')
                                    <span class="badge bg-danger rounded-pill px-3">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Rusak
                                    </span>
                                @elseif($kondisi === 'Bongkar')
                                    <span class="badge bg-warning text-white rounded-pill px-3">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Bongkar
                                    </span>
                                @elseif($kondisi === 'Tidak Terpakai')
                                    <span class="badge bg-secondary rounded-pill px-3">
                                        Tidak Terpakai
                                    </span>
                                @elseif($kondisi === 'Hilang')
                                    <span class="badge bg-dark rounded-pill px-3">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Hilang
                                    </span>
                                @elseif($kondisi === 'Tidak Teridentifikasi')
                                    <span class="badge bg-dark rounded-pill px-3">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Tidak Teridentifikasi
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">
                                        @if($kondisiBuruk)
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                        @endif

                                        {{ $kondisi ?? 'Lainnya' }}
                                    </span>
                                @endif

                                @if($kondisiBerubah && !$kondisiBuruk)
                                    <small class="d-block text-muted mt-1">
                                        Sebelumnya:
                                        {{ $kondisiSistem }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if($kondisi === 'Hilang')
                                    <span class="text-muted small">
                                        <i class="fas fa-minus-circle me-1"></i>
                                        Tidak ada lokasi
                                    </span>
                                @elseif($lokasiBerubah)
                                    <span class="text-warning fw-bold small">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $lokasiTemuanNama }}
                                    </span>

                                    <small class="d-block text-muted">
                                        Beda dari sistem
                                    </small>
                                @else
                                    <span class="text-dark small">
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                        {{ $lokasiTemuanNama }}
                                    </span>
                                @endif
                            </td>

                            @if($isAdmin)
                                <td class="col-divisi">
                                    <span class="text-dark small">
                                        {{ $aset?->resolved_divisi_name ?? '-' }}
                                    </span>
                                </td>

                                <td class="col-dept">
                                    <span class="text-dark small">
                                        {{ $aset?->resolved_department_name ?? '-' }}
                                    </span>
                                </td>
                            @endif

                            <td>
                                @if($detail->foto_temuan)
                                    <img
                                        src="{{ Storage::url($detail->foto_temuan) }}"
                                        alt="Foto temuan {{ $aset?->nomor_aset ?? 'aset' }}"
                                        class="img-temuan"
                                        role="button"
                                        onclick="window.open(this.src, '_blank')"
                                    >
                                @else
                                    <span class="text-muted small">
                                        <i class="fas fa-image me-1"></i>
                                        -
                                    </span>
                                @endif
                            </td>

                            <td>
                                <small class="fw-semibold text-dark">
                                    {{ $detail->dicekOleh?->firstname ?? '-' }}
                                    {{ $detail->dicekOleh?->lastname ?? '' }}
                                </small>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>

                                    {{ $detail->created_at
                                        ? \Carbon\Carbon::parse($detail->created_at)->format('d M, H:i')
                                        : '-' }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
