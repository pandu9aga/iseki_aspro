@extends('layouts.public')
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
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure_public') }}">Procedure</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('procedure_public.area.index', ['Name_Tractor' => $tractor]) }}">{{ $tractor }}</a></li>
        </ol>
    </nav>
    <br>

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            <div class="row mb-4">
                <div class="col-6">
                    <img src="{{ asset($photoTractor ?? 'storage/tractors/default.png') }}" alt="{{ $tractor }}" 
                        style="max-width: 100px; max-height: 100px; width: auto; height: auto;">
                </div>
            </div>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Total Procedure</th>
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
                                    <a href="{{ route('procedure_public.procedure.index', ['Name_Tractor' => $a->Name_Tractor, 'Name_Area' => $a->Name_Area]) }}" class="text-primary font-weight-bold">
                                        {{ $a->Name_Area }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    {{ \App\Models\Procedure::where('Name_Tractor', $a->Name_Tractor)->where('Name_Area', $a->Name_Area)->count() }}
                                </p>
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
