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

                <!-- Tombol Back -->
                <div class="mb-4">
                    <a class="btn btn-primary" href="{{ route('base') }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>
                </div>

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
                @endphp

                <!-- Temuan List Table -->
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">Daftar Temuan</h6>
                            <span class="badge bg-gradient-primary">{{ $temuans->count() }} Temuan</span>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table id="example" class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">No</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prosedur</th>
                                        <th class="text-left text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Item Prosedur</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Member</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Temuan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Penanganan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($temuans as $index => $temuan)
                                        @php
                                            $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                                        @endphp
                                        <tr class="row-data">
                                            <td class="align-middle text-center ps-2">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $index + 1 }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="text-xs font-weight-bold">{{ $temuan->ListReport ? $temuan->ListReport->Name_Tractor : '-' }}</span>
                                                    <span class="text-xxs text-secondary">{{ $temuan->ListReport ? $temuan->ListReport->Name_Area : '' }}</span>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">{{ $temuan->ListReport->Name_Procedure ?? '-' }}</span>
                                            </td>
                                            <td class="align-middle text-left ps-3">
                                                <span class="text-xs">{{ $temuan->ListReport->Item_Procedure ?? '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">{{ $temuan->ListReport->report->member->nama ?? '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">{{ $temuan->Time_Temuan ? Carbon::parse($temuan->Time_Temuan)->format('d/m/Y') : '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($temuan->Time_Penanganan)
                                                    <span class="text-xs">{{ Carbon::parse($temuan->Time_Penanganan)->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-xs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($temuan->Status_Temuan)
                                                    <span class="badge badge-sm bg-gradient-success">
                                                        <i class="material-symbols-rounded text-xs me-1">check_circle</i>Selesai
                                                    </span>
                                                @elseif($object->Is_Submit_Penanganan)
                                                    <span class="badge badge-sm bg-gradient-info">
                                                        <i class="material-symbols-rounded text-xs me-1">schedule</i>Menunggu Validasi
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-warning">
                                                        <i class="material-symbols-rounded text-xs me-1">pending</i>Menunggu Penanganan
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center pe-2">
                                                @if($temuan->ListReport)
                                                    <a href="{{ route('auditor-report.temuan_show', ['Id_Temuan' => $temuan->Id_Temuan]) }}" class="btn btn-sm btn-info mb-0" title="Lihat Detail">
                                                        <i class="material-symbols-rounded text-sm">visibility</i>
                                                    </a>
                                                @else
                                                    <span class="text-xs text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="material-symbols-rounded text-5xl text-secondary opacity-5 mb-3">search_off</i>
                                                    <span class="text-secondary text-sm font-weight-bold">
                                                        Tidak ada temuan pada tanggal {{ \Carbon\Carbon::parse($date ?? \Carbon\Carbon::today())->format('d F Y') }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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

        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge i {
            font-size: 0.875rem;
        }

        /* .bg-gradient-success {
            background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
            box-shadow: 0 2px 4px rgba(67, 160, 71, 0.3);
        }

        .bg-gradient-info {
            background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);
            box-shadow: 0 2px 4px rgba(26, 115, 232, 0.3);
        }

        .bg-gradient-warning {
            background: linear-gradient(195deg, #FFA726 0%, #FB8C00 100%);
            box-shadow: 0 2px 4px rgba(251, 140, 0, 0.3);
        }

        .bg-gradient-primary {
            background: linear-gradient(195deg, #5e72e4 0%, #825ee4 100%);
        }

        .btn-info {
            background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.4);
        } */

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
            @if($temuans->count() > 0)
                $('#example').DataTable();
            @else
                console.log('No data to display in DataTables');
            @endif
        });
    </script>
@endsection
