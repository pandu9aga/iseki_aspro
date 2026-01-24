@extends('layouts.auditor')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Training</h3>
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
                            Start Training -
                            <a class="text-primary" href="{{ route('report_auditor.list', ['year' => \Carbon\Carbon::parse($report->Start_Report)->format('Y'), 'month' => \Carbon\Carbon::parse($report->Start_Report)->format('m')]) }}">
                                {{ \Carbon\Carbon::parse($report->Start_Report)->format('d-m-Y') }}
                            </a>
                        </div>
                        <div>Member - <a class="text-primary" href="{{ route('list_report_auditor', ['Id_Report' => $report->Id_Report]) }}">{{ $report->member->nama }}</a></div>
                    </div>
                </div>
            </div>
            <br>

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
                            <span class="text-secondary">Training List: </span>
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
