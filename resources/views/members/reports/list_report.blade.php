@extends('layouts.member')
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

            <!-- Tombol Back -->
            <button class="btn btn-primary mx-3" onclick="history.back()">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Time Report</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Time Approvement</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $list_reports as $l )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-xs text-primary">
                                        {{ $l->Name_Tractor }} - {{ $l->Name_Area }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-xs text-primary">
                                        {{ $l->Name_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="mb-0">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-xs text-secondary">
                                        {{ $l->Item_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-xs text-secondary">
                                        {{ $l->Time_List_Report }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="mb-0">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-xs text-secondary">
                                        {{ $l->Time_Approvement }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('report_list_member.pdf.editor', ['Id_List_Report' => $l->Id_List_Report]) }}" class="text-primary text-xs mx-1">
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
@endsection