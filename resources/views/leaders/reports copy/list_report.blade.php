@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Report</h3>
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
                        <div>Team - <a class="text-primary" href="{{ route('process', ['Id_Team' => $report->process->team->Id_Team]) }}">{{ $report->process->team->Name_Team }}</a></div>
                        <div>Process - <a class="text-primary" href="{{ route('reporter', ['Id_Process' => $report->process->Id_Process]) }}">{{ $report->process->Name_Process }}</a></div>
                        <div>Member - <a class="text-primary" href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}">{{ $report->member->nama }}</a></div>
                    </div>
                </div>
            </div>
            <br>

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addReportModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Time Report</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Leader Approvement</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Auditor Approvement</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $list_reports as $l )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs text-primary">
                                        {{ $l->Name_Tractor }} - {{ $l->Name_Area }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs text-primary">
                                        {{ $l->Name_Procedure }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="mb-0">
                                    <span class="text-xs text-secondary">
                                        {{ $l->Item_Procedure }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs text-secondary">
                                        {{ $l->Time_List_Report }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs text-secondary">
                                        {{ $l->Time_Approved_Leader }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <span class="text-xs text-secondary">
                                        {{ $l->Time_Approved_Auditor }}
                                    </span>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('report.detail', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-primary text-xs mx-1"
                                    {{-- onclick="previewPdf('{{ asset('storage/reports/' . $l->Name_Area . '/' . $l->report->process->Name_Process . '/' . $l->report->Id_Member . '_' . \Carbon\Carbon::parse($l->report->Time_Created_Report)->format('Y-m-d_H-i-s') . '/' . $l->Name_Procedure . '.pdf') }}', '{{ $l->Name_Procedure }}')" --}}
                                    >
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add Report -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('report.store') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addReportModalLabel">Add Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_Report" value="{{ $report->Id_Report }}">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Team</label>
                        <input type="text" class="form-control" name="Name_Team" value="{{ $report->process->team->Name_Team }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Process</label>
                        <input type="text" class="form-control" name="Name_Process" value="{{ $report->process->Name_Process }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Member</label>
                        <input type="text" class="form-control" name="Name_Member" value="{{ $report->member->nama }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Procedures</label>
                        <select class="form-control select2" name="Name_Procedure[]" multiple required>
                            @foreach ($procedures as $procedure)
                                <option value="{{ $procedure->Name_Procedure }}">{{ $procedure->Name_Procedure }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-2">Submit</button>
                    <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
<link href="{{asset('assets/css/select2.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
new DataTable('#example');
</script>
<script>
    $('#addReportModal').on('shown.bs.modal', function () {
        // Untuk select dengan name="Name_Procedure[]"
        $('select[name="Name_Procedure[]"]').select2({
            dropdownParent: $('#addReportModal'),
            placeholder: "Select Procedure",
            allowClear: true
        });

        // Tambahkan class form-control ke Select2 input yang terlihat
        $('.select2').each(function () {
            let $select = $(this);
            let $select2Container = $select.next('.select2-container');
            $select2Container.find('.select2-selection').addClass('form-control');
        });
    });
</script>
@endsection
