@extends('layouts.user')
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

            <!-- Tombol Add -->
            <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addReportModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Time</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $reports as $r )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs mb-0">
                                    <a href="{{ route('report_list_user', ['Id_Report' => $r->Id_Report]) }}" class="text-primary text-xs mx-1">
                                        {{ $r->Name_Tractor }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs mb-0">
                                    <a href="{{ route('report_list_user', ['Id_Report' => $r->Id_Report]) }}" class="text-primary text-xs mx-1">
                                        {{ $r->Name_Area }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs mb-0">
                                    <a href="{{ route('report_list_user', ['Id_Report' => $r->Id_Report]) }}" class="text-secondary text-xs mx-1">
                                        {{ $r->Time_Report }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('report_list_user', ['Id_Report' => $r->Id_Report]) }}" class="text-primary text-xs mx-1">
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
            <form action="{{ route('report_user.store') }}" role="form" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addReportModalLabel">Add Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Tractor</label>
                        <select class="form-control" name="Name_Tractor" id="tractor-select" required>
                            <option value="">- Select Tractor -</option>
                            @foreach ($tractors as $tractor)
                                <option value="{{ $tractor->Name_Tractor }}">{{ $tractor->Name_Tractor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Area</label>
                        <select class="form-control" name="Name_Area" id="area-select" required>
                            <option value="">- Select Area -</option>
                            <!-- Option area akan diisi dengan JS -->
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
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
<script>
    document.getElementById('tractor-select').addEventListener('change', function() {
        const tractor = this.value;
        const areaSelect = document.getElementById('area-select');
        
        areaSelect.innerHTML = `<option value="">Loading...</option>`;

        fetch(`/get-areas/${encodeURIComponent(tractor)}`)
            .then(response => response.json())
            .then(data => {
                areaSelect.innerHTML = `<option value="">Select Area</option>`;
                data.forEach(area => {
                    areaSelect.innerHTML += `<option value="${area.Name_Area}">${area.Name_Area}</option>`;
                });
            })
            .catch(err => {
                console.error(err);
                areaSelect.innerHTML = `<option value="">Error loading areas</option>`;
            });
    });
</script>
@endsection