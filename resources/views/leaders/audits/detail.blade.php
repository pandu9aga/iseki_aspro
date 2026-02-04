@extends('layouts.leader')

@section('content')
    <header class="header-2">
        <div class="page-header min-vh-45 relative" style="background-image: url('{{ asset('assets/img/bg2.jpg') }}')">
            <span class="mask bg-gradient-primary opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 text-center mx-auto">
                        <h1 class="text-white pt-3 mt-n5">Daily Audit List</h1>
                        <p class="lead text-white mt-3">{{ $auditorName }} - {{ date('d F Y', strtotime($date)) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
        <div class="container">
            <div class="row">
                <div class="col-12 py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">Found {{ count($audits) }} Audits</h3>
                        <a href="{{ route('audit.monthly', ['year' => date('Y', strtotime($date)), 'month' => date('m', strtotime($date))]) }}"
                            class="btn btn-outline-primary mb-0">Back to Summary</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Report
                                        Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reporter
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Procedure</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Audit
                                        Time</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($audits as $audit)
                                    <tr>
                                        <td>
                                            <p class="text-sm font-weight-normal mb-0">{{ $audit->report->Start_Report }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-normal mb-0">
                                                {{ $audit->report->member->nama ?? 'Unknown' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-normal mb-0">{{ $audit->Name_Tractor }}</p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-info">{{ $audit->Name_Procedure }}</span>
                                            <p class="text-xs text-secondary mb-0">{{ $audit->Item_Procedure }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-normal mb-0">{{ $audit->Time_Approved_Auditor }}</p>
                                        </td>
                                        <td>
                                            <a href="{{ route('report.detail', $audit->Id_List_Report) }}"
                                                class="btn btn-link text-primary p-0" data-bs-toggle="tooltip" title="View PDF">
                                                <i class="material-symbols-rounded text-lg">picture_as_pdf</i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if(count($audits) == 0)
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No audits found for this day.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection