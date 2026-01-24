@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Missing PIC Procedures</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('missing') }}">Missing PIC</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('missing.area.index', ['Name_Tractor' => $tractor]) }}">{{ $tractor }}</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('missing.procedure.index', ['Name_Tractor' => $tractor, 'Name_Area' => $area]) }}">{{ $area }}</a></li>
        </ol>
    </nav>
    <br>

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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

            <div class="row mb-4">
                <div class="col-6">
                    <img src="{{ asset($photoTractor ?? 'storage/tractors/default.png') }}" alt="{{ $tractor }}" 
                        style="max-width: 100px; max-height: 100px; width: auto; height: auto;">
                </div>
            </div>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $procedures as $p )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="#" class="text-primary"
                                        onclick="previewPdf('{{ asset('storage/procedures/' . $p->Name_Tractor . '/' . $p->Name_Area . '/' . $p->Name_Procedure . '.pdf') }}?t={{ time() }}', '{{ $p->Name_Procedure }}')">
                                        {{ $p->Name_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="text-xs text-secondary">
                                    {{ $p->Item_Procedure ?: '-' }}
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal"
                                    onclick="setAssign({{ $p->Id_Procedure }}, '{{ $p->Name_Procedure }}')">
                                    <i class="material-symbols-rounded text-xs">add_task</i> Assign to Training
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Assign to Training -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="assignForm" method="POST" action="{{ route('missing.assign.training') }}">
                @csrf
                <input type="hidden" name="Id_Procedure" id="assign-procedure-id">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="assignModalLabel">
                        Assign to Training: <span id="assign-procedure-name"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Select Training</label>
                        
                        <select class="form-control select2" name="Id_Report[]" id="training-select" multiple="multiple" required>
                            @foreach($trainings as $training)
                                <option value="{{ $training->Id_Report }}">
                                    {{ \Carbon\Carbon::parse($training->Start_Report)->format('d M Y') }} - 
                                    {{ $training->member->nama ?? 'N/A' }} 
                                    ({{ $training->member->nik ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-2">
                        <i class="material-symbols-rounded text-sm">check_circle</i>
                        Assign to Selected Training
                    </button>
                    <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview PDF -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white" id="previewModalLabel">Preview Procedure <span id="title"></span></h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdf-frame" src="" width="100%" height="600px" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
<!-- Select2 CSS -->
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<!-- Select2 JS -->
<script src="{{ asset('assets/js/select2.min.js') }}"></script>

<script>
new DataTable('#example');

function setAssign(procedureId, procedureName) {
    document.getElementById('assign-procedure-id').value = procedureId;
    document.getElementById('assign-procedure-name').textContent = procedureName;
    
    // Reset Select2
    $('#training-select').val(null).trigger('change');
}

function previewPdf(fileUrl, title) {
    document.getElementById('pdf-frame').src = fileUrl;
    document.getElementById('title').textContent = '( ' + title + ' )';
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

$('#assignModal').on('shown.bs.modal', function() {
    $('select[name="Id_Report[]"]').select2({
        dropdownParent: $('#assignModal'),
        placeholder: "Select Training",
        allowClear: true
    });
    $('.select2').each(function() {
        $(this).next('.select2-container').find('.select2-selection').addClass('form-control');
    });
});

// Reset form button
$('#resetFormBtn').on('click', function() {
    $('select[name="Id_Report[]"]').val(null).trigger('change');
});
</script>
@endsection