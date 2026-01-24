@extends('layouts.member')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
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

            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <div>Member - <span class="text-primary">{{ $member->nama }}</span></div>
                    </div>
                </div>
            </div>
            <br>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Start Training</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $reports as $r )
                        <tr onclick="window.location='{{ route('report_list_member', ['Id_Report' => $r->Id_Report]) }}'" class="row-data">
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs mb-0">{{ \Carbon\Carbon::parse($r->Start_Report)->format('d-m-Y') }}</p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
<style>
    .row-data {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .row-data:hover {
        background-color: #e91e63 !important;
    }

    .row-data:hover td {
        color: white !important;
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection
