@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Jobdesc - {{ \Carbon\Carbon::parse($targetDate)->format('d-m-Y') }}</h3>
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
                        <div>Tanggal Absensi - <span class="text-primary">{{ \Carbon\Carbon::parse($targetDate)->format('d-m-Y') }}</span></div>
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

            <!-- Tombol Back -->
            <a class="btn btn-primary my-3" href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}">
                <span style="padding-left: 30px; padding-right: 30px;"><b><-</b> Back to Jobdesc</span>
            </a>

            <!-- Daily Jobs & Replacements -->
            <div class="row mt-2">
                <div class="col-md-12 mb-4">
                    <div class="card p-3 shadow-sm border">
                        <h6 class="text-primary font-weight-bolder">Pengganti ({{ \Carbon\Carbon::parse($targetDate)->format('d-m-Y') }})</h6>
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
                                                    <a href="{{ route('list_report_replacement', ['Id_Report_Replacement' => $rep['id_report_replacement']]) }}" class="btn btn-xs btn-info text-white mt-2 mb-0">
                                                        <i class="fa fa-list me-1"></i> Lihat List Prosedur {{ $rep['replacement_name'] }}
                                                    </a>
                                                @elseif($canCopyJobdesc)
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
                            <p class="text-xs text-muted">Tidak ada daily jobs pada tanggal ini.</p>
                        @endforelse
                    </div>
                </div>
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
        background-color: #e91e63 !important;
        color: white !important;
        transform: translateY(-5px);
    }

    .hover-card:hover b {
        color: white !important;
    }
</style>
@endsection
