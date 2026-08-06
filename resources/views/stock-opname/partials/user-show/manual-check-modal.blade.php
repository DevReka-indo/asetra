<div
    class="modal fade"
    id="stockOpnameModal"
    tabindex="-1"
    aria-labelledby="stockOpnameModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div
                class="modal-header border-0 pt-4 px-4 pb-3"
                style="background-color: #253070;"
            >
                <h5
                    class="modal-title fw-bold text-white"
                    id="stockOpnameModalLabel"
                >
                    <i class="fas fa-clipboard-check me-2"></i>
                    Form Temuan Stock Opname
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Tutup"
                ></button>
            </div>

            <form id="stockOpnameForm" enctype="multipart/form-data">
                @csrf

                <input
                    type="hidden"
                    id="so_session_id"
                    name="stock_opname_id"
                    value="{{ $session->id }}"
                >

                <input
                    type="hidden"
                    id="so_aset_id"
                    name="aset_id"
                >

                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3">
                        <small>
                            Memproses Aset:
                            <strong id="scanned_aset_display"></strong>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label
                            for="so_kondisi"
                            class="form-label fw-bold small"
                            style="color: #253070;"
                        >
                            Kondisi Fisik Saat Ini
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="kondisi_temuan"
                            id="so_kondisi"
                            class="form-select shadow-sm rounded-3"
                            required
                        >
                            <option value="">-- Pilih Kondisi --</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Bongkar">Bongkar</option>
                            <option value="Tidak Terpakai">Tidak Terpakai</option>
                            <option value="Hilang">Hilang</option>
                            <option value="Tidak Teridentifikasi">
                                Tidak Teridentifikasi
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label
                            for="so_lokasi"
                            class="form-label fw-bold small"
                            style="color: #253070;"
                        >
                            Lokasi Fisik Saat Ini
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="lokasi_temuan"
                            id="so_lokasi"
                            class="form-select shadow-sm rounded-3"
                            required
                        >
                            <option value="">-- Pilih Lokasi --</option>

                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->lokasi_id }}">
                                    {{ $lokasi->nama_lokasi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label
                            for="so_foto"
                            class="form-label fw-bold small"
                            style="color: #253070;"
                        >
                            Foto Bukti Fisik
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="file"
                            name="foto_temuan"
                            id="so_foto"
                            class="form-control shadow-sm rounded-3"
                            accept="image/*"
                            capture="environment"
                            required
                        >

                        <small class="text-muted mt-1 d-block">
                            Langsung dari kamera atau pilih file.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label
                            for="so_keterangan"
                            class="form-label fw-bold small"
                            style="color: #253070;"
                        >
                            Keterangan (Opsional)
                        </label>

                        <textarea
                            name="keterangan"
                            id="so_keterangan"
                            class="form-control shadow-sm rounded-3"
                            rows="2"
                            placeholder="Tambahkan catatan jika perlu..."
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button
                        type="button"
                        class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn text-white rounded-pill px-4 fw-bold shadow-sm"
                        id="btnSubmitOpname"
                        style="background-color: #253070;"
                    >
                        <i class="fas fa-save me-2"></i>
                        Simpan Temuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
