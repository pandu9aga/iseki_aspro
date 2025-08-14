@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg3.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Member</h3>
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

            <!-- Tombol Data Team -->
            <a class="btn btn-primary mx-3" href="{{ route('team_data') }}">
                <span style="padding-left: 50px; padding-right: 50px;">Data Team</span>
            </a>
            <br><br>

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <!-- Tombol Add -->
            <button class="btn btn-secondary mx-3" data-bs-toggle="modal" data-bs-target="#importMemberModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>++</b> Import</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">NIK</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $members as $member )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-primary mb-0">{{ $member->NIK_Member }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-secondary mb-0">{{ $member->Name_Member }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editMemberModal"
                                        onclick="setEditMember({{ $member }})">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteMemberModal"
                                        onclick="setDeleteMember({{ $member }})">
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

<!-- Modal Add Member -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('member.create') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addMemberModalLabel">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="NIK_Member" value="" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="Name_Member" value="" required>
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

<!-- Modal Edit Member -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editMemberForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="editMemberModalLabel">Edit Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_Member" id="edit-id">

                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="NIK_Member" id="edit-nik" required>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="Name_Member" id="edit-name" required>
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

<!-- Modal Delete Member -->
<div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteMemberForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white" id="deleteMemberModalLabel">Delete Member</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this member:</p>
                    <table>
                        <tr>
                            <td>NIK</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-member-nik"></b></td>
                        </tr>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-member-name"></b></td>
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

<!-- Modal Import Member -->
<div class="modal fade" id="importMemberModal" tabindex="-1" aria-labelledby="importMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('member.import') }}" role="form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="importMemberModalLabel">Import Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Import Excel</label>
                        <input type="file" class="form-control" name="excel" required accept=".xls,.xlsx">
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
    function setEditMember(member) {
        // Set form action
        const form = document.getElementById('editMemberForm');
        form.action = '/iseki_aspro/public/member/update/' + member.Id_Member; // Sesuaikan route-mu

        // Isi data
        document.getElementById('edit-id').value = member.Id_Member;
        document.getElementById('edit-nik').value = member.NIK_Member;
        document.getElementById('edit-name').value = member.Name_Member;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#editMemberModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDeleteMember(member) {
        // Set nama ke <b>
        document.getElementById('delete-member-nik').textContent = member.NIK_Member;
        document.getElementById('delete-member-name').textContent = member.Name_Member;

        // Set action form
        const form = document.getElementById('deleteMemberForm');
        form.action = `/iseki_aspro/public/member/delete/${member.Id_Member}`; // Sesuaikan dengan rute sebenarnya jika beda
    }
</script>
@endsection