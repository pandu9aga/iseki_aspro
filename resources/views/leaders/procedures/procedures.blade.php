@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Procedure</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure') }}">Procedure</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure.area.index', ['Name_Tractor' => $tractor]) }}">{{ $tractor }}</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('procedure.procedure.index', ['Name_Tractor' => $tractor, 'Name_Area' => $area]) }}">{{ $area }}</a></li>
        </ol>
    </nav>
    <br>

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

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2 mb-md-0">
                        <!-- Tombol Add di kiri -->
                        <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addModal">
                            <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
                        </button>
                    </div>
                    <div class="col-12 col-md-3 offset-md-6">
                        <!-- Tombol Item di kanan -->
                        <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#itemModal">
                            <span style="padding-left: 50px; padding-right: 50px;"><i class="material-symbols-rounded">docs_add_on</i> Item</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Area</th>
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
                                    <a href="{{ route('procedure.area.index', ['Name_Tractor' => $p->Name_Tractor]) }}" class="text-primary">
                                        {{ $p->Name_Tractor }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="{{ route('procedure.procedure.index', ['Name_Tractor' => $p->Name_Tractor, 'Name_Area' => $p->Name_Area]) }}" class="text-primary">
                                        {{ $p->Name_Area }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="#" class="text-primary"
                                        onclick="previewPdf('{{ asset('storage/procedures/' . $p->Name_Tractor . '/' . $p->Name_Area . '/' . $p->Name_Procedure . '.pdf') }}', '{{ $p->Name_Procedure }}')">
                                        {{ $p->Name_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="text-xs">
                                    <a href="#" class="text-secondary"
                                        onclick="previewPdf('{{ asset('storage/procedures/' . $p->Name_Tractor . '/' . $p->Name_Area . '/' . $p->Name_Procedure . '.pdf') }}', '{{ $p->Name_Procedure }}')">
                                        {{ $p->Item_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editModal"
                                        onclick="setEdit({{ $p }})">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#uploadModal"
                                        onclick="setUpload({{ $p }})">
                                        <i class="material-symbols-rounded">upload_file</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        onclick="setDelete({{ $p }})">
                                        <i class="material-symbols-rounded">delete</i>
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

<!-- Modal Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('procedure.procedure.create') }}" role="form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" value="{{ $tractor }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Name Area</label>
                        <input type="text" class="form-control" name="Name_Area" value="{{ $area }}" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Upload Procedure</label>
                        <input type="file" class="form-control" name="File_Procedure[]" accept="application/pdf" multiple required>
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="editUserModalLabel">Edit Procedure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" id="edit-tractor" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Area</label>
                        <input type="text" class="form-control" name="Name_Area" id="edit-area" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Procedure</label>
                        <input type="text" class="form-control" name="Name_Procedure" id="edit-procedure" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Item Procedure</label>
                        <input type="text" class="form-control" name="Item_Procedure" id="edit-item">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-2">Update</button>
                    <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white" id="deleteUserModalLabel">Delete Procedure</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this procedure:</p>
                    <table>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-name"></b></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-danger w-100 my-2">Delete</button>
                    <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="uploadUserModalLabel">Re-Upload Procedure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" id="upload-tractor" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Area</label>
                        <input type="text" class="form-control" name="Name_Area" id="upload-area" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Procedure</label>
                        <input type="text" class="form-control" name="Name_Procedure" id="upload-procedure" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Item Procedure</label>
                        <input type="text" class="form-control" name="Item_Procedure" id="upload-item" readonly>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Upload Procedure</label>
                        <input type="file" class="form-control" name="File_Procedure" accept="application/pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-2">Update</button>
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

<!-- Modal Item -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('procedure.procedure.item') }}" role="form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Multiple Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Name_Tractor" value="{{ $tractor }}">
                    <input type="hidden" name="Name_Area" value="{{ $area }}">
                    <label class="form-label">Paste Procedure Items (support multiple row)</label>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Item</label>
                        <textarea class="form-control" name="Item_Tractors" required></textarea>
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
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
<script>
    function setEdit(data) {
        // Set form action
        const form = document.getElementById('editForm');
        form.action = '/procedure/tractor/area/procedure/update/' + data.Id_Procedure; // Sesuaikan route-mu

        // Isi data
        document.getElementById('edit-tractor').value = data.Name_Tractor;
        document.getElementById('edit-area').value = data.Name_Area;
        document.getElementById('edit-procedure').value = data.Name_Procedure;
        document.getElementById('edit-item').value = data.Item_Procedure;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#editModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDelete(data) {
        // Set nama ke <b>
        document.getElementById('delete-name').textContent = data.Name_Procedure;

        // Set action form
        const form = document.getElementById('deleteForm');
        form.action = `/procedure/tractor/area/procedure/delete/${data.Id_Procedure}`; // Sesuaikan dengan rute sebenarnya jika beda
    }

    function setUpload(data) {
        // Set form action
        const form = document.getElementById('uploadForm');
        form.action = '/procedure/tractor/area/procedure/upload/' + data.Id_Procedure; // Sesuaikan route-mu

        // Isi data
        document.getElementById('upload-tractor').value = data.Name_Tractor;
        document.getElementById('upload-area').value = data.Name_Area;
        document.getElementById('upload-procedure').value = data.Name_Procedure;
        document.getElementById('upload-item').value = data.Item_Procedure;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#uploadModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }
</script>
<script>
  function previewPdf(fileUrl, title) {
    document.getElementById('pdf-frame').src = fileUrl;
    document.getElementById('title').textContent = '( ' + title + ' )';

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  }
</script>
@endsection