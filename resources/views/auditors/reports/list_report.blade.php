@extends('layouts.auditor')
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
                            <a class="text-primary" href="{{ route('report_auditor.list', ['year' => \Carbon\Carbon::parse($report->Start_Report)->format('Y'), 'month' => \Carbon\Carbon::parse($report->Start_Report)->format('m')]) }}">
                                {{ \Carbon\Carbon::parse($report->Start_Report)->format('d-m-Y') }}
                            </a>
                        </div>
                        <div>Member - <a class="text-primary" href="{{ route('list_report_auditor', ['Id_Report' => $report->Id_Report]) }}">{{ $report->member->Name_Member }}</a></div>
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
                <div class="col-md-6 mb-4">
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
                                        <tr>
                                            <td>{{ $abs->tanggal }}</td>
                                            <td><span class="badge bg-gradient-info">{{ $abs->kategori }}</span></td>
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

                <!-- Daily Jobs & Replacements -->
                <div class="col-md-6 mb-4">
                    <div class="card p-3 shadow-sm border">
                        <h6 class="text-primary font-weight-bolder">Pengganti (Month {{ \Carbon\Carbon::parse($report->Start_Report)->format('m-Y') }})</h6>
                        @forelse($dailyJobsData as $item)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="text-xs">
                                    <b>Tanggal:</b> {{ \Carbon\Carbon::parse($item['daily_job']->Production_Date_Plan)->format('d-m-Y') }}
                                </div>
                                <div class="text-xs mt-1">
                                    @forelse($item['replacements'] as $rep)
                                        <div class="ms-2 mt-1 p-2 bg-light rounded">
                                            <div><b>NIK / Nama Pengganti:</b> {{ $rep['replacement_nik'] }} - <span class="text-primary">{{ $rep['replacement_name'] }}</span></div>
                                            <div><b>Sequence (Replacements):</b> {{ $rep['sequence_no_plan'] ?? '-' }} | <b>Prod Date:</b> {{ $rep['production_date_plan'] ?? '-' }}</div>
                                            <div><b>Type Plan (Podium):</b> <span class="badge bg-secondary">{{ $rep['type_plan'] ?? '-' }}</span></div>
                                            <div><b>Mapped Tractor:</b> {{ implode(', ', $rep['mapped_tractors']) ?: '-' }}</div>

                                            @if(!empty($rep['mapped_tractors']))
                                                @if(!empty($rep['is_copied']) && !empty($rep['id_report_replacement']))
                                                    @php
                                                        $targetRoute = session('Id_Type_User') == 1 ? route('list_report_replacement_auditor', ['Id_Report_Replacement' => $rep['id_report_replacement']]) : route('list_report_replacement', ['Id_Report_Replacement' => $rep['id_report_replacement']]);
                                                    @endphp
                                                    <a href="{{ $targetRoute }}" class="btn btn-xs btn-info text-white mt-2 mb-0">
                                                        <i class="fa fa-list me-1"></i> Lihat List Prosedur {{ $rep['replacement_name'] }}
                                                    </a>
                                                @elseif(in_array(session('Id_Type_User'), [1, 2]))
                                                    <form action="{{ route('report.copy_replacement') }}" method="POST" class="mt-2">
                                                        @csrf
                                                        <input type="hidden" name="Id_Report" value="{{ $report->Id_Report }}">
                                                        <input type="hidden" name="replacement_nik" value="{{ $rep['replacement_nik'] }}">
                                                        <input type="hidden" name="sequence_no_plan" value="{{ $rep['sequence_no_plan'] }}">
                                                        <input type="hidden" name="production_date_plan" value="{{ $rep['production_date_plan'] }}">
                                                        <input type="hidden" name="type_plan" value="{{ $rep['type_plan'] }}">
                                                        @foreach($rep['mapped_tractors'] as $tr)
                                                            <input type="hidden" name="mapped_tractors[]" value="{{ $tr }}">
                                                        @endforeach
                                                        <button type="submit" class="btn btn-xs btn-primary mb-0" onclick="return confirm('Copy prosedur {{ implode(', ', $rep['mapped_tractors']) }} ke member pengganti ({{ $rep['replacement_name'] }})?')">
                                                            <i class="fa fa-copy me-1"></i> Copy Prosedur ke {{ $rep['replacement_name'] }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted"> (Tidak ada replacement)</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-muted">Tidak ada data daily jobs pada bulan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                @foreach ( $tractorReports as $tractor )
                <div class="col-md-3 col-lg-2">
                    <div class="bg-gray-100 border-radius-xl p-2 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <a href="{{ route('list_report_detail_auditor', ['Id_Report' => $Id_Report, 'Name_Tractor' => $tractor['Name_Tractor']]) }}">
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

