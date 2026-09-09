@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">List Absensi</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">

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

            <div class="row">
                <!-- Data Absensi -->
                <div class="col-12">
                    <div class="card p-3 shadow-sm border">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-primary font-weight-bolder">Data Absensi Member (Month {{ \Carbon\Carbon::parse($report->Start_Report)->format('m-Y') }})</h6>
                            <a href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}" class="btn btn-sm bg-gradient-secondary mb-0">
                                <i class="material-symbols-rounded opacity-6 me-1 text-sm">arrow_back</i> Kembali
                            </a>
                        </div>
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
        </div>
    </section>
</div>
@endsection