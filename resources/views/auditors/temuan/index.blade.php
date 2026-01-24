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
                <a class="btn btn-primary mx-3" href="{{ route('base') }}">
                    <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
                </a>

                <br><br>

                <!-- Date Filter -->
                <div class="row mb-4">
                    <div class="col-lg-6 col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('auditor-report.temuan_index') }}" id="dateFilterForm">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-6">
                                            <input type="date" class="form-control" name="date" id="dateInput" value="{{ $date ?? '' }}" placeholder="Pilih tanggal">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex gap-2 ms-3">
                                                <button class="btn btn-primary mb-0" type="submit">
                                                    <i class="material-symbols-rounded text-sm">search</i> Filter
                                                </button>
                                                @if($date)
                                                    <a href="{{ route('auditor-report.temuan_index') }}" class="btn btn-secondary mb-0">
                                                        <i class="material-symbols-rounded text-sm">close</i> Reset
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            @if($date)
                                                <small class="text-muted">
                                                    <i class="material-symbols-rounded text-xs">calendar_today</i>
                                                    Menampilkan temuan pada: <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong>
                                                </small>
                                            @else
                                                <small class="text-muted">
                                                    <i class="material-symbols-rounded text-xs">list</i>
                                                    Menampilkan semua temuan
                                                </small>
                                            @endif
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
                <div class="table-responsive p-0">
                    <table id="example" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Procedure</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item Procedure</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Member</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tanggal Temuan</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tanggal Penanganan</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($temuans as $index => $temuan)
                                <tr class="row-data">
                                    <td class="align-middle text-center">
                                        <span class="text-xs font-weight-bold text-secondary">{{ $index + 1 }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $temuan->ListReport ? $temuan->ListReport->Name_Tractor . ' - ' . $temuan->ListReport->Name_Area : '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $temuan->ListReport->Name_Procedure ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle text-left">
                                        <span class="text-xs">{{ $temuan->ListReport->Item_Procedure ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $temuan->ListReport->report->member->nama ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $temuan->Time_Temuan ? Carbon::parse($temuan->Time_Temuan)->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $temuan->Time_Penanganan ? Carbon::parse($temuan->Time_Penanganan)->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($temuan->Time_Penanganan && $temuan->Status_Temuan)
                                            <span class="badge badge-sm bg-gradient-success">Selesai</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($temuan->ListReport)
                                            <a href="{{ route('auditor-report.temuan_report', ['Id_List_Report' => $temuan->Id_List_Report]) }}" class="btn btn-sm btn-info mb-0">
                                                <i class="material-symbols-rounded text-sm">visibility</i> View
                                            </a>
                                        @else
                                            <span class="text-xs text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <span class="text-secondary">
                                            @if($date)
                                                Tidak ada temuan pada tanggal ini
                                            @else
                                                Belum ada temuan yang di-submit
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('style')
    <link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
    <style>
        .row-data {
            transition: background-color 0.2s;
        }

        .row-data:hover {
            background-color: #f8f9fa !important;
        }

        #dateFilterForm .form-control {
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        #dateFilterForm .btn {
            border-radius: 0.5rem;
            padding: 0.625rem 1.5rem;
        }

        #dateFilterForm .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        #dateFilterForm small {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .d-flex.gap-2 {
            gap: 0.5rem;
        }

        .badge {
            padding: 0.45rem 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .bg-gradient-success {
            background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(195deg, #FFA726 0%, #FB8C00 100%);
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
