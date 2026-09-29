@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Prosedur Pengganti</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <div>
                        Start Jobdesc -
                        <span class="text-primary">
                            {{ \Carbon\Carbon::parse($report->Start_Report)->format('d-m-Y') }}
                        </span>
                    </div>
                    <div>Member Pengganti - <span class="text-primary">{{ $repMember->Name_Member ?? $reportReplacement->NIK_Replacement }}</span></div>
                    <div>Tractor - <span class="badge bg-secondary">{{ $reportReplacement->Name_Tractor }}</span></div>
                </div>
            </div>

            <!-- Tombol Back -->
            <a class="btn btn-primary my-3" href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}">
                <span style="padding-left: 30px; padding-right: 30px;"><b><-</b> Back to Jobdesc</span>
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
                            @if($isSaiful)
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width:10%">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $list_reports as $l )
                        <tr onclick="window.location='{{ route('report.replacement_detail', ['Id_List_Report_Replacement' => $l->Id_List_Report_Replacement]) }}'" class="row-data">
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
                                        {{ $l->display_name }}
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
                                        {{ $l->Time_List_Report ?? '-' }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Time_Approved_Leader ?? '-' }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs">
                                        {{ $l->Time_Approved_Auditor ?? '-' }}
                                    </span>
                                </p>
                            </td>
                            @if($isSaiful)
                            <td class="align-middle text-center" onclick="event.stopPropagation()">
                                @if($l->Time_Approved_Leader || $l->Time_Approved_Auditor || $l->Time_List_Report)
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-link text-warning p-0" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false" title="Reset Approval">
                                            <i class="material-symbols-rounded">restart_alt</i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            @if($l->Time_Approved_Auditor)
                                                <li>
                                                    <form action="{{ route('report.replacement.reset', $l->Id_List_Report_Replacement) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="role" value="auditor">
                                                        <button type="submit" class="dropdown-item text-warning"
                                                            onclick="return confirm('Reset approval AUDITOR untuk {{ addslashes($l->display_name) }}?\nCoretan member & leader akan dipertahankan.')">
                                                            <i class="material-symbols-rounded me-1" style="font-size:16px">verified_user</i> Reset Auditor
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($l->Time_Approved_Leader)
                                                <li>
                                                    <form action="{{ route('report.replacement.reset', $l->Id_List_Report_Replacement) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="role" value="leader">
                                                        <button type="submit" class="dropdown-item text-orange"
                                                            onclick="return confirm('Reset approval LEADER & AUDITOR untuk {{ addslashes($l->display_name) }}?\nCoretan member akan dipertahankan.')">
                                                            <i class="material-symbols-rounded me-1" style="font-size:16px">manage_accounts</i> Reset Leader
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('report.replacement.reset', $l->Id_List_Report_Replacement) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="role" value="all">
                                                    <button type="submit" class="dropdown-item text-danger"
                                                        onclick="return confirm('Reset SEMUA approval untuk {{ addslashes($l->display_name) }}?\nSemua coretan dan foto akan dihapus.')">
                                                        <i class="material-symbols-rounded me-1" style="font-size:16px">delete_sweep</i> Reset Semua
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                            @endif
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
