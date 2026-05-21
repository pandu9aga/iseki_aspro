<script>
    // Auto-load statistics on page load
    document.addEventListener('DOMContentLoaded', function () {
        loadMonthlyStatistics();
        loadMissingStatistics();

        // Refresh missing stats every 5 minutes
        setInterval(loadMissingStatistics, 300000);
    });

    // Load monthly statistics
    async function loadMonthlyStatistics() {
        const month = document.getElementById('monthPicker').value;
        const container = document.getElementById('monthlyStatsContainer');

        try {
            const response = await fetch(`{{ route('auditor-temuan.statistics.monthly') }}?month=${month}`);
            const data = await response.json();

            container.innerHTML = `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <strong><i class="material-symbols-rounded text-sm align-middle me-1">calendar_month</i> 
                        ${data.monthName}</strong> - Total Temuan: <span class="badge bg-primary">${data.total}</span>
                    </div>
                </div>
            </div>
            
            <div class="row g-3">
                <!-- Revisi Prosedur -->
                <div class="col-md-6 col-lg-2">
                    <div class="card stat-card revisi mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="stat-number text-primary">${data.categories['Revisi prosedur'].total}</div>
                                <div class="stat-label">Revisi Prosedur</div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Belum Penanganan</span>
                                    <span class="status-badge belum">${data.categories['Revisi prosedur'].belum_penanganan}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Menunggu Validasi</span>
                                    <span class="status-badge menunggu">${data.categories['Revisi prosedur'].menunggu_validasi}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Sudah Tervalidasi</span>
                                    <span class="status-badge selesai">${data.categories['Revisi prosedur'].sudah_tervalidasi}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Perakitan Tak Sesuai -->
                <div class="col-md-6 col-lg-2">
                    <div class="card stat-card perakitan mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="stat-number text-success">${data.categories['Perakitan tak sesuai'].total}</div>
                                <div class="stat-label">Perakitan Tak Sesuai</div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Belum Penanganan</span>
                                    <span class="status-badge belum">${data.categories['Perakitan tak sesuai'].belum_penanganan}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Menunggu Validasi</span>
                                    <span class="status-badge menunggu">${data.categories['Perakitan tak sesuai'].menunggu_validasi}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Sudah Tervalidasi</span>
                                    <span class="status-badge selesai">${data.categories['Perakitan tak sesuai'].sudah_tervalidasi}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Shiyousho Tak Sesuai -->
                <div class="col-md-6 col-lg-2">
                    <div class="card stat-card shiyousho mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="stat-number text-danger">${data.categories['Shiyousho tak sesuai'].total}</div>
                                <div class="stat-label">Shiyousho Tak Sesuai</div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Belum Penanganan</span>
                                    <span class="status-badge belum">${data.categories['Shiyousho tak sesuai'].belum_penanganan}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Menunggu Validasi</span>
                                    <span class="status-badge menunggu">${data.categories['Shiyousho tak sesuai'].menunggu_validasi}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Sudah Tervalidasi</span>
                                    <span class="status-badge selesai">${data.categories['Shiyousho tak sesuai'].sudah_tervalidasi}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tidak Perlu Penanganan -->
                <div class="col-md-6 col-lg-2">
                    <div class="card stat-card tidakperlu mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="stat-number text-secondary">${data.categories['Tidak perlu penanganan'].total}</div>
                                <div class="stat-label">Tidak Perlu Penanganan</div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Belum Penanganan</span>
                                    <span class="status-badge belum">${data.categories['Tidak perlu penanganan'].belum_penanganan}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Menunggu Validasi</span>
                                    <span class="status-badge menunggu">${data.categories['Tidak perlu penanganan'].menunggu_validasi}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Sudah Tervalidasi</span>
                                    <span class="status-badge selesai">${data.categories['Tidak perlu penanganan'].sudah_tervalidasi}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lain-lain -->
                <div class="col-md-6 col-lg-2">
                    <div class="card stat-card lainlain mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="stat-number text-warning">${data.categories['Lain-lain'].total}</div>
                                <div class="stat-label">Lain-lain</div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Belum Penanganan</span>
                                    <span class="status-badge belum">${data.categories['Lain-lain'].belum_penanganan}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Menunggu Validasi</span>
                                    <span class="status-badge menunggu">${data.categories['Lain-lain'].menunggu_validasi}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-xs">Sudah Tervalidasi</span>
                                    <span class="status-badge selesai">${data.categories['Lain-lain'].sudah_tervalidasi}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        } catch (error) {
            console.error('Error loading monthly statistics:', error);
            container.innerHTML = `
            <div class="alert alert-danger">
                <i class="material-symbols-rounded text-sm align-middle me-1">error</i>
                Gagal memuat statistik. Silakan coba lagi.
            </div>
        `;
        }
    }

    // Filter by missing type
    function filterMissing(type, el) {
        const month = document.getElementById('monthPicker').value;
        const form = document.getElementById('monthFilterForm');
        const activeType = '{{ $missingType ?? '' }}';

        document.getElementById('monthInput').value = month;

        let missingInput = document.getElementById('missingTypeInput');
        if (!missingInput) {
            missingInput = document.createElement('input');
            missingInput.type = 'hidden';
            missingInput.name = 'missing_type';
            missingInput.id = 'missingTypeInput';
            form.appendChild(missingInput);
        }
        // Toggle off if clicking the same active filter
        missingInput.value = (activeType === type) ? '' : type;

        document.querySelectorAll('.missing-stat-item').forEach(el => el.classList.remove('active-filter'));
        if (missingInput.value && el) el.classList.add('active-filter');

        form.submit();
    }

    // Load missing statistics
    async function loadMissingStatistics() {
        const container = document.getElementById('missingStatsContainer');
        const month = document.getElementById('monthPicker').value;

        try {
            const response = await fetch(`{{ route('auditor-temuan.statistics.missing') }}?month=${month}`);
            const data = await response.json();

            const activeType = '{{ $missingType ?? '' }}';

            container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="missing-stat-item text-center ${activeType === 'uncategorized' ? 'active-filter' : ''}" data-missing-type="uncategorized" onclick="filterMissing('uncategorized', this)">
                        <i class="material-symbols-rounded text-3xl text-warning mb-2">folder_off</i>
                        <div class="stat-number text-warning">${data.uncategorized}</div>
                        <div class="stat-label">Belum Dikategorikan (>1 Hari)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="missing-stat-item text-center ${activeType === 'no_penanganan' ? 'active-filter' : ''}" data-missing-type="no_penanganan" onclick="filterMissing('no_penanganan', this)">
                        <i class="material-symbols-rounded text-3xl text-danger mb-2">build_circle</i>
                        <div class="stat-number text-danger">${data.no_penanganan}</div>
                        <div class="stat-label">Belum Ada Penanganan (>1 Hari)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="missing-stat-item text-center ${activeType === 'no_validasi' ? 'active-filter' : ''}" data-missing-type="no_validasi" onclick="filterMissing('no_validasi', this)">
                        <i class="material-symbols-rounded text-3xl text-dark mb-2">verified</i>
                        <div class="stat-number text-dark">${data.no_validasi}</div>
                        <div class="stat-label">Belum Di Validasi (>1 Hari)</div>
                    </div>
                </div>
            </div>
            
            ${data.total_missing > 0 ? `
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="material-symbols-rounded text-sm align-middle me-1">info</i>
                    <strong>Perhatian!</strong> Ada ${data.total_missing} temuan yang memerlukan tindakan segera.
                </div>
            ` : `
                <div class="alert alert-success mt-3 mb-0">
                    <i class="material-symbols-rounded text-sm align-middle me-1">check_circle</i>
                    Tidak ada missing temuan saat ini. Semua temuan dalam kondisi baik.
                </div>
            `}
        `;
        } catch (error) {
            console.error('Error loading missing statistics:', error);
            container.innerHTML = `
            <div class="alert alert-danger">
                <i class="material-symbols-rounded text-sm align-middle me-1">error</i>
                Gagal memuat statistik missing temuan. Silakan coba lagi.
            </div>
        `;
        }
    }
</script>