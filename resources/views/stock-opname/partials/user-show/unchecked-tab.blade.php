<div
    class="tab-pane fade show active"
    id="pills-belum"
    role="tabpanel"
    aria-labelledby="pills-belum-tab"
    tabindex="0"
>
    @if($belumDicek->isEmpty())
        <div class="empty-row">
            <i class="fas fa-check-double text-success d-block mb-2"></i>

            <h5 class="fw-bold text-dark mt-2 mb-1">
                Semua Aset Sudah Dicek!
            </h5>

            <p class="text-muted small mb-0">
                Bagus, tidak ada aset yang tertinggal di scope Anda.
            </p>
        </div>
    @else
        <div class="row g-2 align-items-end mb-3">
            <div style="width: 100px;">
                <label class="form-label fw-bold small text-muted text-uppercase">
                    Entries
                </label>

                <select
                    class="form-select form-select-sm rounded-3 custom-entries-select"
                    data-table="dtBelum"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            @if($isAdmin)
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        Divisi
                    </label>

                    <select
                        class="form-select form-select-sm rounded-3 custom-divisi-filter"
                        data-table="dtBelum"
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
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        Departemen
                    </label>

                    <select
                        class="form-select form-select-sm rounded-3 custom-dept-filter"
                        data-table="dtBelum"
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
                <label class="form-label fw-bold small text-muted text-uppercase">
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
                        class="form-control border-0 shadow-none bg-transparent custom-search-input"
                        data-table="dtBelum"
                        placeholder="Cari nomor atau nama..."
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table mb-0" id="tableBelum">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th>Aset</th>
                        <th>Kategori</th>
                        <th>Lokasi Terakhir</th>

                        @if($isAdmin)
                            <th class="col-divisi">Divisi</th>
                            <th class="col-dept">Departemen/Unit</th>
                        @endif

                        <th>Kondisi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($belumDicek as $aset)
                        <tr
                            data-divisi="{{ $aset->resolved_divisi_name }}"
                            data-dept="{{ $aset->resolved_department_name }}"
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
                                            {{ $aset->nomor_aset }}
                                        </div>

                                        <div class="nama">
                                            {{ $aset->nama_aset }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="text-dark small">
                                    {{ $aset->kategoriAset->nama ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <i class="fas fa-map-marker-alt text-muted me-1"></i>

                                <span class="text-dark small">
                                    {{ $aset->lokasi->nama_lokasi ?? '-' }}
                                </span>
                            </td>

                            @if($isAdmin)
                                <td class="col-divisi">
                                    <span class="text-dark small">
                                        {{ $aset->resolved_divisi_name }}
                                    </span>
                                </td>

                                <td class="col-dept">
                                    <span class="text-dark small">
                                        {{ $aset->resolved_department_name }}
                                    </span>
                                </td>
                            @endif

                            <td>
                                @switch($aset->status_kondisi)
                                    @case('Baik')
                                        <span class="badge bg-success rounded-pill px-3">
                                            Baik
                                        </span>
                                        @break

                                    @case('Rusak')
                                        <span class="badge bg-danger rounded-pill px-3">
                                            Rusak
                                        </span>
                                        @break

                                    @case('Bongkar')
                                        <span class="badge bg-warning text-white rounded-pill px-3">
                                            Bongkar
                                        </span>
                                        @break

                                    @case('Tidak Terpakai')
                                        <span class="badge bg-secondary rounded-pill px-3">
                                            Tidak Terpakai
                                        </span>
                                        @break

                                    @case('Hilang')
                                    @case('Tidak Teridentifikasi')
                                        <span class="badge bg-dark rounded-pill px-3">
                                            {{ $aset->status_kondisi }}
                                        </span>
                                        @break

                                    @default
                                        <span class="badge bg-secondary rounded-pill px-3">
                                            {{ $aset->status_kondisi ?? 'Lainnya' }}
                                        </span>
                                @endswitch
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn so-btn-outline so-action-btn btn-cek-manual"
                                    data-aset-id="{{ $aset->id }}"
                                    data-aset-nomor="{{ $aset->nomor_aset }}"
                                    data-aset-nama="{{ $aset->nama_aset }}"
                                    title="Input temuan manual untuk aset ini"
                                >
                                    <i class="fas fa-pen-to-square me-1"></i>
                                    Cek Manual
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
