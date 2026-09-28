@extends('layouts.leader')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Jobdesc</h3>
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
                                <div class="alert alert-danger text-white text-xs alert-dismissible fade show"
                                    role="alert">
                                    {{ $error }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success text-white text-xs alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning text-white text-xs alert-dismissible fade show" role="alert">
                                {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif
                @if (session('info'))
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info text-white text-xs alert-dismissible fade show" role="alert">
                                {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $selectedYear = request('year', now()->year);
                    $months = [
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ];
                    $nowYear = now()->year;
                    $prevMonth = now()->subMonth()->month;
                    $prevMonthYear = now()->subMonth()->year;
                    $currentMonth = now()->month;
                    $currentYear = now()->year;
                @endphp

                <div class="container mt-4">
                    <!-- Form Pilih Tahun -->
                    <form method="GET" class="d-flex align-items-center gap-2 my-3">
                        <div class="input-group input-group-outline is-filled" style="width: 150px">
                            <label class="form-label" for="year">Select Year</label>
                            <select name="year" id="year" class="form-control">
                                @for ($year = $nowYear; $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Apply</button>
                    </form>

                    <!-- Tombol Duplikasi Template Modal Trigger -->
                    <div class="my-3">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#copyJobdescModal">
                            <i class="fa fa-copy me-1"></i> Copy Jobdesc Antar Bulan
                        </button>
                    </div>

                    <!-- Daftar Bulan (Cukup 1x) -->
                    <div class="row">
                        @foreach ($months as $num => $name)
                            <div class="col-6 col-md-3 col-lg-2 mb-3">
                                <a href="{{ route('reporter', ['year' => $selectedYear, 'month' => $num]) }}">
                                    <div class="border rounded text-center py-3 bg-light card-hover">
                                        <strong>{{ $name }} {{ $selectedYear }}</strong>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Copy Jobdesc Antar Bulan (Diletakkan di luar card blur agar backdrop tidak menutupi modal) -->
    <div class="modal fade" id="copyJobdescModal" tabindex="-1" aria-labelledby="copyJobdescModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('report.create.template') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="copyJobdescModalLabel">
                            <i class="fa fa-copy me-1"></i> Copy Jobdesc Antar Bulan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info text-white text-xs mb-3">
                            <i class="fa fa-info-circle me-1"></i> <strong>Catatan:</strong>
                            <ul class="mb-0 ps-3">
                                <li>File PDF prosedur akan disalin bersih langsung dari folder master prosedur.</li>
                                <li>Data persetujuan leader, member, dan auditor akan <strong>dikosongkan</strong>.</li>
                            </ul>
                        </div>

                        <h6 class="text-primary font-weight-bold mb-2">1. Bulan & Tahun Sumber (Asal Copy)</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="input-group input-group-outline is-filled">
                                    <label class="form-label">Bulan Sumber</label>
                                    <select name="source_month" class="form-control" required>
                                        @foreach ($months as $mNum => $mName)
                                            <option value="{{ (int)$mNum }}" {{ $prevMonth == (int)$mNum ? 'selected' : '' }}>
                                                {{ $mName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-outline is-filled">
                                    <label class="form-label">Tahun Sumber</label>
                                    <select name="source_year" class="form-control" required>
                                        @for ($y = $nowYear + 1; $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ $prevMonthYear == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-success font-weight-bold mb-2">2. Bulan & Tahun Target (Tujuan Copy)</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="input-group input-group-outline is-filled">
                                    <label class="form-label">Bulan Target</label>
                                    <select name="target_month" class="form-control" required>
                                        @foreach ($months as $mNum => $mName)
                                            <option value="{{ (int)$mNum }}" {{ $currentMonth == (int)$mNum ? 'selected' : '' }}>
                                                {{ $mName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-outline is-filled">
                                    <label class="form-label">Tahun Target</label>
                                    <select name="target_year" class="form-control" required>
                                        @for ($y = $nowYear + 1; $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyalin seluruh jobdesc dari bulan sumber ke bulan target?')">
                            <i class="fa fa-check me-1"></i> Proses Salin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <link href="{{ asset('assets/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <style>
        .card-hover:hover {
            background-color: #e91e63 !important;
            color: white !important;
        }
        #copyJobdescModal {
            z-index: 1060 !important;
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        new DataTable('#example');
    </script>
@endsection
