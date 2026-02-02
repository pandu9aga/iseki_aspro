@extends('layouts.auditor')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Missing Temuan - Perlu Perhatian</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="text-white">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="text-white">{{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Back Button -->
                <div class="mb-4">
                    <a class="btn btn-primary" href="{{ route('auditor-report.temuan_index') }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back to List
                    </a>
                </div>

                <!-- Summary Card -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-gradient-warning text-white rounded-circle me-3">
                                        <i class="material-symbols-rounded">folder_off</i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Belum Dikategorikan</h6>
                                        <p class="text-sm text-muted mb-0">Lebih dari 3 hari</p>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-gradient-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                            {{ $uncategorizedTemuans->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-gradient-danger text-white rounded-circle me-3">
                                        <i class="material-symbols-rounded">build_circle</i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Belum Ada Penanganan</h6>
                                        <p class="text-sm text-muted mb-0">Lebih dari 15 hari</p>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-gradient-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                            {{ $noPenangananTemuans->count() }}
                                        </span>
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

                <!-- Uncategorized Temuans Section -->
                @if($uncategorizedTemuans->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header pb-0 bg-gradient-warning">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded text-white me-2">folder_off</i>
                                <h6 class="mb-0 text-white">Temuan Belum Dikategorikan (Lebih dari 3 Hari)</h6>
                                <span class="badge bg-white text-warning ms-2">{{ $uncategorizedTemuans->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" id="uncategorizedTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Temuan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hari Tertunda</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prosedur</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Member</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($uncategorizedTemuans as $index => $temuan)
                                            @php
                                                $daysOverdue = floor(
                                                    Carbon::parse($temuan->Time_Temuan)->floatDiffInDays(Carbon::now())
                                                );
                                                $urgencyClass = $daysOverdue > 3 ? 'danger' : 'warning';
                                            @endphp
                                            <tr class="row-data">
                                                <td class="align-middle text-center">
                                                    <span class="text-xs font-weight-bold">{{ $index + 1 }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ Carbon::parse($temuan->Time_Temuan)->format('d/m/Y H:i') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge badge-sm bg-gradient-{{ $urgencyClass }}">
                                                        {{ $daysOverdue }} hari
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->Name_Tractor }} - {{ $temuan->ListReport->Name_Area }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->Name_Procedure }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->report->member->nama ?? '-' }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('auditor-report.temuan_show', ['Id_Temuan' => $temuan->Id_Temuan]) }}" 
                                                       class="btn btn-sm btn-warning mb-0" title="Lihat Detail">
                                                        <i class="material-symbols-rounded text-sm">visibility</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- No Penanganan Temuans Section -->
                @if($noPenangananTemuans->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header pb-0 bg-gradient-danger">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded text-white me-2">build_circle</i>
                                <h6 class="mb-0 text-white">Temuan Belum Ada Penanganan (Lebih dari 15 Hari)</h6>
                                <span class="badge bg-white text-danger ms-2">{{ $noPenangananTemuans->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" id="noPenangananTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe Temuan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Temuan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hari Tertunda</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prosedur</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Member</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($noPenangananTemuans as $index => $temuan)
                                            @php
                                                $daysOverdue = floor(
                                                    Carbon::parse($temuan->Time_Temuan)->floatDiffInDays(Carbon::now())
                                                );
                                                $urgencyClass = $daysOverdue > 15 ? 'danger' : 'warning';
                                            @endphp
                                            <tr class="row-data">
                                                <td class="align-middle text-center">
                                                    <span class="text-xs font-weight-bold">{{ $index + 1 }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if($temuan->Tipe_Temuan)
                                                        <span class="badge badge-sm bg-gradient-info">{{ $temuan->Tipe_Temuan }}</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ Carbon::parse($temuan->Time_Temuan)->format('d/m/Y H:i') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge badge-sm bg-gradient-{{ $urgencyClass }}">
                                                        {{ $daysOverdue }} hari
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->Name_Tractor }} - {{ $temuan->ListReport->Name_Area }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->Name_Procedure }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-xs">{{ $temuan->ListReport->report->member->nama ?? '-' }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('auditor-report.temuan_show', ['Id_Temuan' => $temuan->Id_Temuan]) }}" 
                                                       class="btn btn-sm btn-danger mb-0" title="Lihat Detail">
                                                        <i class="material-symbols-rounded text-sm">visibility</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- No Missing Temuans -->
                @if($uncategorizedTemuans->count() === 0 && $noPenangananTemuans->count() === 0)
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="material-symbols-rounded text-5xl text-success mb-3">check_circle</i>
                            <h5 class="text-success">Tidak Ada Missing Temuan</h5>
                            <p class="text-muted">Semua temuan dalam kondisi baik dan terpantau.</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@section('style')
    <link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
    <style>
        .row-data {
            transition: all 0.3s ease;
        }

        .row-data:hover {
            background-color: #fff3cd !important;
            transform: translateX(5px);
        }

        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card {
            border-radius: 1rem;
        }
    </style>
@endsection

@section('script')
    <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
    <script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            @if($uncategorizedTemuans->count() > 0)
                $('#uncategorizedTable').DataTable({
                    order: [[2, 'desc']], // Sort by days overdue
                    pageLength: 25
                });
            @endif

            @if($noPenangananTemuans->count() > 0)
                $('#noPenangananTable').DataTable({
                    order: [[3, 'desc']], // Sort by days overdue
                    pageLength: 25
                });
            @endif
        });
    </script>
@endsection