@extends('layouts.auditor')
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
                    <a class="btn btn-primary" href="{{ route('base') }}">
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
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="month" class="form-control form-control-sm" id="monthPicker" 
                                            value="{{ \Carbon\Carbon::now()->format('Y-m') }}" 
                                            style="width: 150px;">
                                        <button class="btn btn-sm btn-primary" onclick="loadMonthlyStatistics()">
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
                                    <a href="{{ route('auditor-temuan.missing') }}" class="btn btn-sm btn-white">
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

                <!-- Statistics Styles and Scripts -->
                @include('auditors.temuan.partials.statistics-styles')
                @include('auditors.temuan.partials.statistics-scripts')

                <!-- Date Filter -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="{{ route('auditor-report.temuan_index') }}" id="dateFilterForm">
                                    <div class="row align-items-end">
                                        <div class="col-md-4 col-lg-3">
                                            <label for="dateInput" class="form-label text-xs text-uppercase font-weight-bolder mb-2">
                                                <i class="material-symbols-rounded text-xs">calendar_today</i> Filter Tanggal
                                            </label>
                                            <input type="date" class="form-control" name="date" id="dateInput" value="{{ $date ?? \Carbon\Carbon::today()->format('Y-m-d') }}" placeholder="Pilih tanggal">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100 mb-0" type="submit">
                                                <i class="material-symbols-rounded text-sm">filter_alt</i> Apply
                                            </button>
                                        </div>
                                        <div class="col-md-6 col-lg-7 mt-3 mt-md-0">
                                            <div class="alert alert-info mb-0 py-2" role="alert">
                                                <small class="text-dark d-flex align-items-center">
                                                    <i class="material-symbols-rounded text-sm me-1">info</i>
                                                    Menampilkan temuan pada: <strong class="ms-1">{{ \Carbon\Carbon::parse($date ?? \Carbon\Carbon::today())->format('d F Y') }}</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button" role="tab">
                                    Semua <span class="badge bg-primary ms-1">{{ $temuans->count() }}</span>
                                </button>
                            </li>
                            @foreach($tipeTemuanCategories as $category => $items)
                                @if(count($items) > 0)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="{{ Str::slug($category) }}-tab" data-bs-toggle="tab" data-bs-target="#{{ Str::slug($category) }}-content" type="button" role="tab">
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
                                @include('auditors.temuan.partials.temuan-table', ['temuans' => $temuans, 'current_user' => $current_user])
                            </div>

                            <!-- Category Tabs -->
                            @foreach($tipeTemuanCategories as $category => $items)
                                @if(count($items) > 0)
                                    <div class="tab-pane fade" id="{{ Str::slug($category) }}-content" role="tabpanel">
                                        @include('auditors.temuan.partials.temuan-table', ['temuans' => collect($items), 'current_user' => $current_user])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
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

        #dateFilterForm .form-control {
            border-radius: 0.5rem;
            font-size: 0.875rem;
            border: 1px solid #d2d6da;
            padding: 0.625rem 0.75rem;
        }

        #dateFilterForm .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.15);
        }

        #dateFilterForm .btn {
            border-radius: 0.5rem;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #dateFilterForm .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: none;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f0f2f5;
        }

        .table thead th {
            border-bottom: 2px solid #e9ecef;
        }

        .table tbody tr {
            border-bottom: 1px solid #f0f2f5;
        }

        .alert-info {
            background: linear-gradient(195deg, #e3f2fd 0%, #bbdefb 100%);
            border: none;
            border-radius: 0.5rem;
        }

        .form-label {
            color: #344767;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            #dateFilterForm .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .badge {
                padding: 0.375rem 0.625rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endsection

@section('script')
    <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
    <script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables for each tab
            $('.temuan-table').each(function() {
                const table = $(this);
                const rowCount = table.find('tbody tr').length;
                const hasData = table.find('tbody td[colspan]').length === 0;

                if (rowCount > 0 && hasData) {
                    table.DataTable({
                        pageLength: 25,
                        order: [[5, 'desc']],
                        language: {
                            emptyTable: "Tidak ada temuan pada tanggal ini."
                        }
                    });
                } else {
                    table.addClass('table-borderless');
                }
            });
        });
    </script>
@endsection
