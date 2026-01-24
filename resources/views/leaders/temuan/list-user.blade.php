@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Temuan</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="text-white">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="text-white">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
            <a class="btn btn-primary mx-3" href="{{ route('leader-temuan.list') }}">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </a>

            <br><br>

            @php
                $monthNames = [
                    '01' => 'January', '02' => 'February', '03' => 'March',
                    '04' => 'April', '05' => 'May', '06' => 'June',
                    '07' => 'July', '08' => 'August', '09' => 'September',
                    '10' => 'October', '11' => 'November', '12' => 'December'
                ];
                $monthName = $monthNames[$month] ?? $month;
            @endphp

            <h5 class="text-center text-secondary mb-4">Daftar Auditor dengan Temuan di: <span class="text-primary">{{ $monthName }} {{ $year }}</span></h5>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Auditor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Total Temuan</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group list reports by auditor (user)
                            $auditorGroups = [];
                            foreach ($listTemuan as $listReport) {
                                foreach ($listReport->Temuans as $temuan) {
                                    $auditorId = $temuan->Id_User;
                                    if (!isset($auditorGroups[$auditorId])) {
                                        $auditorGroups[$auditorId] = [
                                            'user' => $temuan->user,
                                            'total_temuan' => 0
                                        ];
                                    }
                                    $auditorGroups[$auditorId]['total_temuan']++;
                                }
                            }
                        @endphp

                        @forelse ($auditorGroups as $auditorId => $data)
                            <tr class="row-data">
                                <td class="align-middle text-center">
                                    <span class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-xs text-secondary">{{ $data['user']->Name_User }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-sm bg-gradient-info">{{ $data['total_temuan'] }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('leader-temuan.list-user', ['Id_User' => $auditorId]) }}" class="btn btn-sm btn-info mb-0">
                                        <i class="material-symbols-rounded text-sm">visibility</i> View Temuan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <span class="text-secondary">Tidak ada temuan ditemukan untuk bulan ini</span>
                                </td>
                            </tr>
                        @endforelse
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
        transition: background-color 0.2s;
    }

    .row-data:hover {
        background-color: #f8f9fa !important;
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
