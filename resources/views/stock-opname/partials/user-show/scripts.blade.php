<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function () {
        const dataTableOptions = {
            pageLength: 10,
            order: [],
            dom: 'rtip',
            language: {
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                infoEmpty: '',
                zeroRecords: 'Tidak ada data ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Lanjut',
                    previous: 'Kembali'
                }
            }
        };

        const tables = {
            dtBelum: $.fn.DataTable.isDataTable('#tableBelum')
                ? $('#tableBelum').DataTable()
                : ($('#tableBelum').length
                    ? $('#tableBelum').DataTable(dataTableOptions)
                    : null),

            dtDitemukan: $.fn.DataTable.isDataTable('#tableDitemukan')
                ? $('#tableDitemukan').DataTable()
                : ($('#tableDitemukan').length
                    ? $('#tableDitemukan').DataTable(dataTableOptions)
                    : null)
        };

        const stockOpnameModalElement =
            document.getElementById('stockOpnameModal');

        const stockOpnameModal = stockOpnameModalElement
            ? bootstrap.Modal.getOrCreateInstance(stockOpnameModalElement)
            : null;

        function escapeExactSearch(value) {
            return '^'
                + $.fn.dataTable.util.escapeRegex(value)
                + '$';
        }

        function applyColumnFilter(table, columnSelector, value) {
            if (!table) {
                return;
            }

            table
                .column(columnSelector)
                .search(
                    value === '' ? '' : escapeExactSearch(value),
                    value !== '',
                    false
                )
                .draw();
        }

        function getSelectedFilter(selector) {
            return $(selector).first().val() || '';
        }

        function updateFilteredStats() {
            const selectedDivisi =
                getSelectedFilter('.custom-divisi-filter');

            const selectedDepartment =
                getSelectedFilter('.custom-dept-filter');

            const matchesSelectedFilters = function () {
                const row = $(this);
                const rowDivisi = row.attr('data-divisi') || '';
                const rowDepartment = row.attr('data-dept') || '';

                const matchesDivisi =
                    selectedDivisi === ''
                    || rowDivisi === selectedDivisi;

                const matchesDepartment =
                    selectedDepartment === ''
                    || rowDepartment === selectedDepartment;

                return matchesDivisi && matchesDepartment;
            };

            const uncheckedRows = tables.dtBelum
                ? $(tables.dtBelum.rows().nodes())
                : $();

            const checkedRows = tables.dtDitemukan
                ? $(tables.dtDitemukan.rows().nodes())
                : $();

            const uncheckedCount =
                uncheckedRows.filter(matchesSelectedFilters).length;

            const checkedCount =
                checkedRows.filter(matchesSelectedFilters).length;

            const totalCount = uncheckedCount + checkedCount;

            const progressPercent = totalCount > 0
                ? Math.round((checkedCount / totalCount) * 100)
                : 0;

            $('.so-stat-num-total').text(totalCount);
            $('.so-stat-num-belum').text(uncheckedCount);
            $('.so-stat-num-telah').text(checkedCount);

            $('.count-badge-belum').text(uncheckedCount);
            $('.count-badge-telah').text(checkedCount);

            $('.hero-progress-percent').text(progressPercent + '%');
            $('.hero-progress-telah').text(checkedCount);
            $('.hero-progress-total').text(totalCount);

            const radius = 50;
            const circumference = 2 * Math.PI * radius;
            const offset =
                circumference
                - ((progressPercent / 100) * circumference);

            $('.hero-progress-circle').css(
                'stroke-dashoffset',
                offset
            );
        }

        function handleKondisiChange() {
            const isLost = $('#so_kondisi').val() === 'Hilang';
            const lokasiInput = $('#so_lokasi');
            const fotoInput = $('#so_foto');

            lokasiInput
                .prop('disabled', isLost)
                .prop('required', !isLost);

            fotoInput
                .prop('disabled', isLost)
                .prop('required', !isLost);

            if (isLost) {
                lokasiInput.val('');
                fotoInput.val('');
            }

            lokasiInput
                .closest('.mb-3')
                .find('.text-danger')
                .toggleClass('d-none', isLost);

            fotoInput
                .closest('.mb-3')
                .find('.text-danger')
                .toggleClass('d-none', isLost);
        }

        $('.custom-entries-select').on('change', function () {
            const tableName = $(this).data('table');
            const table = tables[tableName];

            if (!table) {
                return;
            }

            table
                .page
                .len(Number.parseInt($(this).val(), 10))
                .draw();
        });

        $('.custom-search-input').on('input', function () {
            const tableName = $(this).data('table');
            const table = tables[tableName];

            if (!table) {
                return;
            }

            table.search($(this).val()).draw();
        });

        $('.custom-divisi-filter').on('change', function () {
            const value = $(this).val() || '';

            $('.custom-divisi-filter').val(value);

            applyColumnFilter(
                tables.dtBelum,
                '.col-divisi',
                value
            );

            applyColumnFilter(
                tables.dtDitemukan,
                '.col-divisi',
                value
            );

            updateFilteredStats();
        });

        $('.custom-dept-filter').on('change', function () {
            const value = $(this).val() || '';

            $('.custom-dept-filter').val(value);

            applyColumnFilter(
                tables.dtBelum,
                '.col-dept',
                value
            );

            applyColumnFilter(
                tables.dtDitemukan,
                '.col-dept',
                value
            );

            updateFilteredStats();
        });

        $('button[data-bs-toggle="pill"]').on(
            'shown.bs.tab',
            function () {
                $.fn.dataTable
                    .tables({
                        visible: true,
                        api: true
                    })
                    .columns
                    .adjust();
            }
        );

        $('#so_kondisi').on('change', handleKondisiChange);

        /*
         * Event delegation wajib digunakan karena DataTables menggambar
         * ulang baris ketika pagination, pencarian, atau filter berubah.
         */
        $('#tableBelum tbody').on(
            'click',
            '.btn-cek-manual',
            function () {
                const button = $(this);
                const asetId = button.data('aset-id');
                const asetNomor = button.data('aset-nomor');
                const asetNama = button.data('aset-nama');
                const form = document.getElementById('stockOpnameForm');

                if (!form || !stockOpnameModal) {
                    return;
                }

                form.reset();

                $('#so_aset_id').val(asetId);

                $('#scanned_aset_display').text(
                    asetNomor + ' - ' + asetNama
                );

                handleKondisiChange();
                stockOpnameModal.show();
            }
        );

        $('#stockOpnameForm').on('submit', async function (event) {
            event.preventDefault();

            const form = this;
            const submitButton =
                document.getElementById('btnSubmitOpname');

            if (!submitButton || submitButton.disabled) {
                return;
            }

            const originalContent = submitButton.innerHTML;

            submitButton.disabled = true;
            submitButton.innerHTML =
                '<i class="fas fa-spinner fa-spin me-2"></i>'
                + 'Menyimpan...';

            try {
                const response = await fetch(
                    @json(route('stock-opname.scanStore')),
                    {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                const data = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || 'Temuan gagal disimpan.'
                    );
                }

                stockOpnameModal.hide();

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message || 'Temuan berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                });

                window.location.reload();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message
                        || 'Terjadi kesalahan sistem.'
                });
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalContent;
            }
        });

        updateFilteredStats();
    });
</script>
