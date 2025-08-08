@extends('layouts.auditor')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Training</h3>
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

            @php
                $selectedYear = request('year', now()->year);
                $months = [
                    '01' => 'January', '02' => 'February', '03' => 'March',
                    '04' => 'April', '05' => 'May', '06' => 'June',
                    '07' => 'July', '08' => 'August', '09' => 'September',
                    '10' => 'October', '11' => 'November', '12' => 'December'
                ];
                $nowYear = now()->year;
            @endphp

            <div class="container mt-4">
                <form method="GET" class="d-flex align-items-center gap-2 my-3">
                    <div class="input-group input-group-outline is-filled" style="width: 150px">
                        <label class="form-label" for="year">Select Year</label>
                        <select name="year" id="year" class="form-control">
                            @for ($year = $nowYear; $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex align-items-center" style="height: 100%">
                        <button type="submit" class="btn btn-primary mt-3">Apply</button>
                    </div>
                </form>

                <div class="row">
                    @foreach ($months as $num => $name)
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <a href="{{ route('report_auditor.list', ['year' => $selectedYear, 'month' => $num]) }}">
                                <div class="border rounded text-center py-3 bg-light card-hover">
                                    <strong>{{ $name }} {{ $selectedYear }}</strong>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Team</th>
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
                                <p class="text-xs text-primary mb-0">{{ $team->Name_Team }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('process', ['Id_Team' => $team->Id_Team]) }}" class="text-primary text-xs mx-1">
                                        <i class="material-symbols-rounded">app_registration</i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> --}}
        </div>
    </section>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
<link href="{{asset('assets/css/select2.min.css')}}" rel="stylesheet">
<style>
    .card-hover:hover {
        background-color: #e91e63 !important;
        color: white !important;
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
new DataTable('#example');
</script>
@endsection