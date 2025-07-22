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
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('procedure.area.index', ['Name_Tractor' => $tractor]) }}">{{ $tractor }}</a></li>
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

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $areas as $a )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="{{ route('procedure.area.index', ['Name_Tractor' => $a->Name_Tractor]) }}" class="text-primary">
                                        {{ $a->Name_Tractor }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="{{ route('procedure.procedure.index', ['Name_Tractor' => $a->Name_Tractor, 'Name_Area' => $a->Name_Area]) }}" class="text-primary">
                                        {{ $a->Name_Area }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editModal"
                                        onclick="setEdit({{ $a }})">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        onclick="setDelete({{ $a }})">
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
            <form action="{{ route('procedure.area.create') }}" role="form" method="POST">
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
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Area</label>
                        <input type="text" class="form-control" name="Name_Area" value="" required>
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
                    <h5 class="modal-title text-white" id="editUserModalLabel">Edit Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" id="edit-tractor" required readonly>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Area</label>
                        <input type="text" class="form-control" name="Name_Area" id="edit-area" required>
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
                    <h4 class="modal-title text-white" id="deleteUserModalLabel">Delete Area</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this area:</p>
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
        form.action = '/procedure/tractor/area/update/' + data.Id_Area; // Sesuaikan route-mu

        // Isi data
        document.getElementById('edit-tractor').value = data.Name_Tractor;
        document.getElementById('edit-area').value = data.Name_Area;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#editModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDelete(data) {
        // Set nama ke <b>
        document.getElementById('delete-name').textContent = data.Name_Area;

        // Set action form
        const form = document.getElementById('deleteForm');
        form.action = `/procedure/tractor/area/delete/${data.Id_Area}`; // Sesuaikan dengan rute sebenarnya jika beda
    }
</script>
@endsection