@extends('layouts.leader')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Temuan</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                @if ($errors->any())
                    <div class="row">
                        @foreach ($errors->all() as $error)
                            <div class="col-12 col-lg-6">
                                <div class="alert alert-danger text-white text-xs alert-dismissible fade show" role="alert">
                                    {{ $error }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="text-white">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Tombol Back -->
                <div class="mb-4">
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>
                </div>

                <!-- Monthly Statistics Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="material-symbols-rounded text-sm align-middle me-1">analytics</i>
                                        Statistik Temuan Bulanan
                                    </h6>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="d-flex align-items-center gap-1">
                                            <button class="btn btn-sm btn-outline-primary px-2 mb-0" onclick="changeStatMonth(-1)" title="Bulan Sebelumnya">
                                                <i class="material-symbols-rounded text-sm">chevron_left</i>
                                            </button>
                                            <input type="month" class="form-control form-control-sm text-center" id="monthPicker"
                                                value="{{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}" style="width: 140px; min-height: 32px;">
                                            <button class="btn btn-sm btn-outline-primary px-2 mb-0" onclick="changeStatMonth(1)" title="Bulan Selanjutnya">
                                                <i class="material-symbols-rounded text-sm">chevron_right</i>
                                            </button>
                                        </div>
                                        <button class="btn btn-sm btn-primary mb-0" onclick="loadMonthlyStatistics()">
                                            <i class="material-symbols-rounded text-sm">refresh</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" id="monthlyStatsContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .stat-card {
                        border-left: 4px solid;
                        transition: all 0.3s ease;
                    }

                    .stat-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    }

                    .stat-card.revisi {
                        border-left-color: #5e72e4;
                    }

                    .stat-card.perakitan {
                        border-left-color: #2dce89;
                    }

                    .stat-card.shiyousho {
                        border-left-color: #f5365c;
                    }

                    .stat-card.tidakperlu {
                        border-left-color: #adb5bd;
                    }

                    .stat-card.lainlain {
                        border-left-color: #fb6340;
                    }

                    .stat-number {
                        font-size: 2rem;
                        font-weight: 700;
                        line-height: 1;
                    }

                    .stat-label {
                        font-size: 0.875rem;
                        color: #8898aa;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }

                    .status-badge {
                        padding: 0.25rem 0.75rem;
                        border-radius: 0.375rem;
                        font-size: 0.75rem;
                        font-weight: 600;
                    }

                    .status-badge.belum {
                        background: #ffeaa7;
                        color: #fdcb6e;
                    }

                    .status-badge.menunggu {
                        background: #74b9ff;
                        color: #0984e3;
                    }

                    .status-badge.selesai {
                        background: #55efc4;
                        color: #00b894;
                    }

                    .missing-stat-item {
                        padding: 1rem;
                        border-radius: 0.5rem;
                        background: #fff3cd;
                        border: 1px solid #ffc107;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }

                    .missing-stat-item:hover {
                        background: #ffeaa7;
                        border-color: #ff9800;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    }

                    .missing-stat-item.active-filter {
                        background: #ffc107;
                        border-color: #e65100;
                    }
                </style>

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
                            const response = await fetch(`{{ route('leader-temuan.statistics.monthly') }}?month=${month}`);
                            const data = await response.json();

                            container.innerHTML = `
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <div class="alert alert-info text-white mb-0">
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
                            const response = await fetch(`{{ route('leader-temuan.statistics.missing') }}?month=${month}`);
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

                    function changeStatMonth(offset) {
                        const monthInput = document.getElementById('monthPicker');
                        let [year, month] = monthInput.value.split('-');
                        let date = new Date(year, parseInt(month) - 1 + offset, 1);
                        
                        let newYear = date.getFullYear();
                        let newMonth = String(date.getMonth() + 1).padStart(2, '0');
                        
                        monthInput.value = `${newYear}-${newMonth}`;
                        loadMonthlyStatistics();
                    }

                    function changeMonth(offset) {
                        const monthInput = document.getElementById('monthInput');
                        let [year, month] = monthInput.value.split('-');
                        let date = new Date(year, parseInt(month) - 1 + offset, 1);
                        
                        let newYear = date.getFullYear();
                        let newMonth = String(date.getMonth() + 1).padStart(2, '0');
                        
                        monthInput.value = `${newYear}-${newMonth}`;
                        document.getElementById('monthFilterForm').submit();
                    }
                </script>

                <!-- Month Filter -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="{{ route('leader-temuan.list') }}" id="monthFilterForm">
                                    <div class="row align-items-end g-3">
                                        <div class="col-12 col-md-5 col-lg-4">
                                            <label for="monthInput"
                                                class="form-label text-xs text-uppercase font-weight-bolder mb-2">
                                                <i class="material-symbols-rounded text-xs">calendar_month</i> Filter
                                                Bulan
                                            </label>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm mb-0 px-3 d-flex align-items-center justify-content-center" onclick="changeMonth(-1)" title="Bulan Sebelumnya" style="height: 38px;">
                                                    <i class="material-symbols-rounded">chevron_left</i>
                                                </button>
                                                <input type="month" class="form-control px-2 text-center" name="month" id="monthInput"
                                                    value="{{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}" style="height: 38px;">
                                                <button type="button" class="btn btn-outline-primary btn-sm mb-0 px-3 d-flex align-items-center justify-content-center" onclick="changeMonth(1)" title="Bulan Selanjutnya" style="height: 38px;">
                                                    <i class="material-symbols-rounded">chevron_right</i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-3">
                                            <label for="statusInput"
                                                class="form-label text-xs text-uppercase font-weight-bolder mb-2">
                                                <i class="material-symbols-rounded text-xs">filter_list</i> Filter Status
                                            </label>
                                            <select class="form-select border px-2" name="status" id="statusInput" style="height: 38px;">
                                                <option value="all" {{ ($statusFilter ?? 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                                <option value="belum_divalidasi" {{ ($statusFilter ?? '') == 'belum_divalidasi' ? 'selected' : '' }}>Belum Divalidasi</option>
                                                <option value="ditolak" {{ ($statusFilter ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                <option value="selesai" {{ ($statusFilter ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-2">
                                            <button class="btn btn-primary w-100 mb-0 d-flex align-items-center justify-content-center" type="submit" style="height: 38px;">
                                                <i class="material-symbols-rounded text-sm me-1">filter_alt</i> Apply
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0 py-2" role="alert">
                                                <small class="text-white d-flex align-items-center">
                                                    <i class="material-symbols-rounded text-sm me-1">info</i>
                                                    Menampilkan temuan pada bulan: <strong
                                                        class="ms-1">{{ \Carbon\Carbon::parse($month ?? \Carbon\Carbon::now())->format('F Y') }}</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Missing Temuan Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-warning">
                            <div class="card-header pb-0 bg-gradient-warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-white">
                                        <i class="material-symbols-rounded text-sm align-middle me-1">warning</i>
                                        Missing Temuan - Perlu Perhatian
                                    </h6>
                                    <a href="{{ route('leader-temuan.missing') }}?month={{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}" class="btn btn-sm btn-white">
                                        <i class="material-symbols-rounded text-sm">arrow_forward</i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                            <div class="card-body" id="missingStatsContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    use Carbon\Carbon;
                    $current_user = \App\Models\User::find(session('Id_User'));
                @endphp

                <!-- Category Tabs -->
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <ul class="nav nav-tabs" id="tipeTemuanTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab"
                                    data-bs-target="#all-content" type="button" role="tab">
                                    Semua <span class="badge bg-primary ms-1">{{ $temuans->count() }}</span>
                                </button>
                            </li>
                            @foreach($tipeTemuanCategories as $category => $items)
                                @if(count($items) > 0)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="{{ Str::slug($category) }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#{{ Str::slug($category) }}-content" type="button" role="tab">
                                            {{ $category }} <span class="badge bg-primary ms-1">{{ count($items) }}</span>
                                        </button>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="tab-content" id="tipeTemuanTabsContent">
                            <!-- All Temuans Tab -->
                            <div class="tab-pane fade show active" id="all-content" role="tabpanel">
                                @include('leaders.temuan.partials.temuan-table', ['temuans' => $temuans, 'current_user' => $current_user])
                            </div>

                            <!-- Category Tabs -->
                            @foreach($tipeTemuanCategories as $category => $items)
                                @if(count($items) > 0)
                                    <div class="tab-pane fade" id="{{ Str::slug($category) }}-content" role="tabpanel">
                                        @include('leaders.temuan.partials.temuan-table', ['temuans' => collect($items), 'current_user' => $current_user])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal for Tipe Temuan -->
    <div class="modal fade" id="tipeTemuanModal" tabindex="-1" aria-labelledby="tipeTemuanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="tipeTemuanModalLabel">Update Tipe Temuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tipeTemuanForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="input-group input-group-outline is-filled mb-3">
                            <label for="tipe_temuan" class="form-label">Tipe Temuan</label>
                            <select class="form-select" id="tipe_temuan" name="tipe_temuan" required>
                                <option value="">Pilih Tipe Temuan</option>
                                <option value="Revisi prosedur">Revisi prosedur</option>
                                <option value="Perakitan tak sesuai">Perakitan tak sesuai</option>
                                <option value="Shiyousho tak sesuai">Shiyousho tak sesuai</option>
                                <option value="Tidak perlu penanganan">Tidak perlu penanganan</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                        </div>
                        <div class="input-group input-group-outline is-filled mb-3" id="customTipeContainer"
                            style="display: none;">
                            <label for="tipe_temuan_custom" class="form-label">Tipe Temuan Custom</label>
                            <input type="text" class="form-control" id="tipe_temuan_custom" name="tipe_temuan_custom"
                                placeholder="Masukkan tipe temuan custom">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
    <style>
        .row-data {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .row-data:hover {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .nav-tabs .nav-link {
            color: #344767;
            font-weight: 600;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #5e72e4;
        }

        .nav-tabs .nav-link.active {
            color: #5e72e4;
            background-color: transparent;
            border-bottom-color: #5e72e4;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('script')
    <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
    <script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTables for each tab
            $('.temuan-table').each(function () {
                const table = $(this);
                const rowCount = table.find('tbody tr').length;
                const hasData = table.find('tbody td[colspan]').length === 0; // bukan pesan "tidak ada data"

                if (rowCount > 0 && hasData) {
                    table.DataTable({
                        pageLength: 100,
                        order: [],
                        language: {
                            emptyTable: "Tidak ada temuan pada tanggal ini."
                        }
                    });
                } else {
                    // Opsional: tampilkan pesan tanpa DataTables
                    table.addClass('table-borderless');
                }
            });

            // Show/hide custom input based on selection
            $('#tipe_temuan').change(function () {
                if ($(this).val() === 'Lain-lain') {
                    $('#customTipeContainer').show();
                    $('#tipe_temuan_custom').prop('required', true);
                } else {
                    $('#customTipeContainer').hide();
                    $('#tipe_temuan_custom').prop('required', false);
                }
            });
        });

        function openTipeTemuanModal(idTemuan, currentTipe) {
            const form = document.getElementById('tipeTemuanForm');
            form.action = `./update-tipe/${idTemuan}`;

            // Set current value
            document.getElementById('tipe_temuan').value = currentTipe || '';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('tipeTemuanModal'));
            modal.show();
        }
    </script>
@endsection