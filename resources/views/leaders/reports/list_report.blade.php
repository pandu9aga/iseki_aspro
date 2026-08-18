@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
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
                            <div class="alert alert-danger text-white text-xs alert-dismissible fade show" role="alert">
                                {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <div>
                            Start Jobdesc -
                            <a class="text-primary" href="{{ route('reporter', ['year' => \Carbon\Carbon::parse($report->Start_Report)->format('Y'), 'month' => \Carbon\Carbon::parse($report->Start_Report)->format('m')]) }}">
                                {{ \Carbon\Carbon::parse($report->Start_Report)->format('d-m-Y') }}
                            </a>
                        </div>
                        <div>Member - <a class="text-primary" href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}">{{ $report->member->Name_Member }}</a></div>
                    </div>
                </div>
            </div>
            <br>

            @if (session('success'))
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="alert alert-success text-white text-xs alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mt-4">
                <!-- Data Absensi -->
                <div class="col-12 mb-4">
                    <div class="card p-3 shadow-sm border">
                        <h6 class="text-primary font-weight-bolder">Data Absensi Member (Month {{ \Carbon\Carbon::parse($report->Start_Report)->format('m-Y') }})</h6>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0 text-xs">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($absensis as $abs)
                                        <tr style="cursor: pointer;" onclick="window.location='{{ route('list_report_daily', ['Id_Report' => $report->Id_Report, 'date' => \Carbon\Carbon::parse($abs->tanggal)->format('Y-m-d')]) }}'">
                                            <td>{{ \Carbon\Carbon::parse($abs->tanggal)->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="badge bg-gradient-info">{{ $abs->kategori }}</span>
                                                <span class="text-xs text-muted">{{ \App\Helpers\MemberHelper::kategoriLabel($abs->kategori, $abs->keterangan ?? null) }}</span>
                                            </td>
                                            <td>{{ $abs->keterangan ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data absensi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                @foreach ( $tractorReports as $tractor )
                <div class="col-md-3 col-lg-2">
                    <div class="bg-gray-100 border-radius-xl p-2 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <a href="{{ route('list_report_detail', ['Id_Report' => $Id_Report, 'Name_Tractor' => $tractor['Name_Tractor']]) }}">
                            <div class="hover-card bg-white border-radius-xl align-items-center d-flex flex-column justify-content-center w-100 p-1 shadow-lg">
                                <div style="width: 180px; height: 180px; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset($tractor['Photo_Tractor'] ?? 'storage/tractors/default.png') }}"
                                        alt="{{ $tractor['Name_Tractor'] }}"
                                        style="max-width: 180px; max-height: 180px; width: auto; height: auto;">
                                </div>
                                <b class="text-primary">{{ $tractor['Name_Tractor'] }}</b>
                            </div>
                        </a>
                        <span class="mt-3">
                            <span class="text-secondary">Jobdesc List: </span>
                            <span class="text-primary">{{ $tractor['Report_Count'] }}</span>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@section('style')
<style>
    .hover-card {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .hover-card:hover {
        background-color: #e91e63 !important; /* Biru Bootstrap */
        color: white !important;
        transform: translateY(-5px);
    }

    .hover-card:hover b {
        color: white !important;
    }
</style>
@endsection

