@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
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
                        <div>Team - <a class="text-primary" href="{{ route('process', ['Id_Team' => $process->team->Id_Team]) }}">{{ $process->team->Name_Team }}</a></div>
                        <div>Process - <a class="text-primary" href="{{ route('reporter', ['Id_Process' => $process->Id_Process]) }}">{{ $process->Name_Process }}</a></div>
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
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Member</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $reports as $report )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-primary mb-0">{{ $report->member->Name_Member }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}" class="text-primary text-xs mx-1">
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
            <form action="{{ route('reporter.create') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addReportModalLabel">Add Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_Process" value="{{ $process->Id_Process }}">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Team</label>
                        <input type="text" class="form-control" name="Name_Team" value="{{ $process->team->Name_Team }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Process</label>
                        <input type="text" class="form-control" name="Name_Process" value="{{ $process->Name_Process }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Members</label>
                        <select class="form-control select2" name="Id_Member[]" multiple required>
                            @foreach ($members as $member)
                                <option value="{{ $member->Id_Member }}">{{ $member->Name_Member }}</option>
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
        // Untuk select dengan name="Id_Member[]"
        $('select[name="Id_Member[]"]').select2({
            dropdownParent: $('#addReportModal'),
            placeholder: "Select Members",
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
<script>
    // // Event listener untuk Team
    // document.getElementById('team-select').addEventListener('change', function () {
    //     const teamId = this.value; // Id_Team tetap untuk form
    //     const teamName = this.options[this.selectedIndex].text; // Ambil Name_Team untuk fetch
    //     const procedureSelect = document.getElementById('procedure-select');

    //     // Kosongkan dulu option
    //     procedureSelect.innerHTML = `<option value="">Loading...</option>`;

    //     if (!teamName || !teamId) {
    //         procedureSelect.innerHTML = `<option value="">Select Procedure</option>`;
    //         $('#procedure-select').val(null).trigger('change');
    //         return;
    //     }

    //     // Ambil data prosedur berdasarkan team
    //     fetch(`/get-procedures/${encodeURIComponent(teamName)}`)
    //         .then(response => response.json())
    //         .then(data => {
    //             procedureSelect.innerHTML = `<option value="">Select Procedure</option>`;
    //             data.forEach(proc => {
    //                 procedureSelect.innerHTML += `<option value="${proc.Name_Procedure}">${proc.Name_Procedure}</option>`;
    //             });

    //             // Re-inisialisasi Select2 setelah update
    //             $('#procedure-select').select2({
    //                 dropdownParent: $('#addReportModal'),
    //                 placeholder: "Select Procedure",
    //                 allowClear: true,
    //                 width: '100%'
    //             });

    //             $('#procedure-select').next('.select2-container').find('.select2-selection').addClass('form-control');
    //         })
    //         .catch(err => {
    //             console.error(err);
    //             procedureSelect.innerHTML = `<option value="">Error loading procedures</option>`;
    //         });
    // });
</script>
@endsection

