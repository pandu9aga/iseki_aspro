@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg3.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Team</h3>
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
            <a class="btn btn-primary mx-3" href="{{ route('team') }}">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Team List</span>
            </a>
            <br><br>

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Team</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $teams as $team )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-secondary mb-0">{{ $team->Name_Team }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editTeamModal"
                                        onclick="setEditTeam({{ $team }})">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                    <a href="#" class="text-primary text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteTeamModal"
                                        onclick="setDeleteTeam({{ $team }})">
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

<!-- Modal Add Team -->
<div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('team_data.create') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addTeamModalLabel">Add Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name Team</label>
                        <input type="text" class="form-control" name="Name_Team" value="" required>
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

<!-- Modal Edit Team -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="editTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editTeamForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="editTeamModalLabel">Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_Team" id="edit-id">

                    <div class="input-group input-group-outline my-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="Name_Team" id="edit-name" required>
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

<!-- Modal Delete Team -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1" aria-labelledby="deleteTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteTeamForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white" id="deleteTeamModalLabel">Delete Team</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to delete this team:</p>
                    <table>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td><b class="text-danger" id="delete-team-name"></b></td>
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
    function setEditTeam(team) {
        // Set form action
        const form = document.getElementById('editTeamForm');
        form.action = '/team_data/update/' + team.Id_Team; // Sesuaikan route-mu

        // Isi data
        document.getElementById('edit-id').value = team.Id_Team;
        document.getElementById('edit-name').value = team.Name_Team;

        // Tambahkan class is-filled agar label naik
        document.querySelectorAll('#editTeamModal .input-group').forEach(group => {
            group.classList.add('is-filled');
        });
    }

    function setDeleteTeam(team) {
        // Set nama ke <b>
        document.getElementById('delete-team-name').textContent = team.Name_Team;

        // Set action form
        const form = document.getElementById('deleteTeamForm');
        form.action = `/team_data/delete/${team.Id_Team}`; // Sesuaikan dengan rute sebenarnya jika beda
    }
</script>
@endsection