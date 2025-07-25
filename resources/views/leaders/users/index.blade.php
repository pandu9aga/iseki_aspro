@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg3.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">User</h3>
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

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Type</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">NIK</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $user as $u )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                @php
                                    $type = $type_user->firstWhere('Id_Type_User', $u->Id_Type_User);
                                @endphp
                                <p class="text-xs text-secondary mb-0">{{ $type ? $type->Name_Type_User : 'Unknown' }}</p>
                            </td>
                            <td class="align-middle text-center" style="text-align: left;">
                                <p class="text-xs text-primary mb-0">{{ $u->NIK_User }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-secondary mb-0">{{ $u->Name_User }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                        onclick="setEditUser({{ $u }})">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                        onclick="setDeleteUser({{ $u }})">
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

<!-- Modal Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('user.create') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="Name_User" value="" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="NIK_User" value="" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="Password_User" value="" required>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="Id_Type_User">
                            @foreach ($type_user as $type)
                                <option value="{{ $type->Id_Type_User }}">
                                    {{ $type->Name_Type_User }}
                                </option>
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

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_User" id="edit-id">

                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="Name_User" id="edit-name" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="NIK_User" id="edit-nik" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="Password_User" id="edit-password" required>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="Id_Type_User" id="edit-type">
                            @foreach ($type_user as $type)
                                <option value="{{ $type->Id_Type_User }}">{{ $type->Name_Type_User }}</option>
                            @endforeach
                        </select>
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

<!-- Modal Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white" id="deleteUserModalLabel">Delete User</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this user:</p>
                    <table>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-user-name"></b></td>
                        </tr>
                        <tr>
                            <td>NIK</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-user-nik"></b></td>
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
    function setEditUser(user) {
        // Set form action
        const form = document.getElementById('editUserForm');
        form.action = '/user/update/' + user.Id_User; // Sesuaikan route-mu

        // Isi data
        document.getElementById('edit-id').value = user.Id_User;
        document.getElementById('edit-name').value = user.Name_User;
        document.getElementById('edit-nik').value = user.NIK_User;
        document.getElementById('edit-password').value = user.Password_User; // kosongkan
        document.getElementById('edit-type').value = user.Id_Type_User;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#editUserModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDeleteUser(user) {
        // Set nama ke <b>
        document.getElementById('delete-user-name').textContent = user.Name_User;
        document.getElementById('delete-user-nik').textContent = user.NIK_User;

        // Set action form
        const form = document.getElementById('deleteUserForm');
        form.action = `/user/delete/${user.Id_User}`; // Sesuaikan dengan rute sebenarnya jika beda
    }
</script>
@endsection