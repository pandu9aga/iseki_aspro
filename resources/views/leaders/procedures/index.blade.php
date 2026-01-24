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
        </ol>
    </nav>

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

            <div class="row mt-4">
                @foreach ( $tractors as $t )
                <div class="col-md-3 col-lg-2">
                    <div class="bg-gray-100 border-radius-xl p-2 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <a href="{{ route('procedure.area.index', ['Name_Tractor' => $t->Name_Tractor]) }}">
                            <div class="hover-card bg-white border-radius-xl align-items-center d-flex flex-column justify-content-center w-100 p-1 shadow-lg">
                                <div style="width: 180px; height: 180px; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset($t->Photo_Tractor ?? 'storage/tractors/default.png') }}"
                                        alt="{{ $t->Name_Tractor }}"
                                        style="max-width: 180px; max-height: 180px; width: auto; height: auto;">
                                </div>
                                <b class="text-primary">{{ $t->Name_Tractor }}</b>
                                
                                <!-- Total Procedure Count -->
                                <div>
                                    <span class="badge bg-primary">
                                        {{ $tractorProcedureCounts[$t->Name_Tractor] ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        <span class="mt-3">
                            <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editModal"
                                onclick="setEdit({{ $t }})">
                                <i class="material-symbols-rounded">app_registration</i>
                            </a>
                            <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                onclick="setDelete({{ $t }})">
                                <i class="material-symbols-rounded">delete</i>
                            </a>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<!-- Modal Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('procedure.tractor.create') }}" role="form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Tractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" value="" required>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Photo Tractor</label>
                        <input type="file" class="form-control" name="Photo_Tractor" accept="image/*" required>
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
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="editUserModalLabel">Edit Tractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Tractor</label>
                        <input type="text" class="form-control" name="Name_Tractor" id="edit-name" required>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Photo Tractor</label>
                        <input type="file" class="form-control" name="Photo_Tractor" accept="image/*">
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
                    <h4 class="modal-title text-white" id="deleteUserModalLabel">Delete Tractor</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this tractor:</p>
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
<style>
    .hover-card {
        transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
    }

    .hover-card:hover {
        background-color: #e91e63 !important;
        color: white !important;
        transform: translateY(-5px);
    }

    .hover-card:hover b {
        color: white !important;
    }

    .hover-card:hover .badge {
        background: linear-gradient(310deg, #ffffff 0%, #f0f0f0 100%) !important;
        color: #e91e63 !important;
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
<script>
    function setEdit(data) {
        const form = document.getElementById('editForm');
        form.action = '/iseki_aspro/public/procedure/tractor/update/' + data.Id_Tractor;

        document.getElementById('edit-name').value = data.Name_Tractor;

        document.querySelectorAll('#editModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDelete(data) {
        document.getElementById('delete-name').textContent = data.Name_Tractor;

        const form = document.getElementById('deleteForm');
        form.action = `/iseki_aspro/public/procedure/tractor/delete/${data.Id_Tractor}`;
    }
</script>
@endsection