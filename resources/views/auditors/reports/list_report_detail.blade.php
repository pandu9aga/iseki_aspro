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

            <!-- Tombol Back -->
            <a class="btn btn-primary mx-3" href="{{ route('list_report_auditor', ['Id_Report' => $report->Id_Report]) }}">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </a>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Check Member</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Leader Approvement</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Auditor Approvement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $list_reports as $l )
                        <tr onclick="window.location='{{ route('report_auditor.detail', ['Id_List_Report' => $l->Id_List_Report]) }}'" class="row-data">
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Name_Tractor }} - {{ $l->Name_Area }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Name_Procedure }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Item_Procedure }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Time_List_Report }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Time_Approved_Leader }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Time_Approved_Auditor }}
                                    </span>
                                </p>
                            </td>
                        </tr>
                        @endforeach
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
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .row-data:hover {
        background-color: #e91e63 !important;
    }

    .row-data:hover td {
        color: white !important;
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection
