@extends('layouts.auditor')
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
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure_auditor') }}">Procedure</a></li>
        </ol>
    </nav>

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            <div class="row mt-4">
                @foreach ( $tractors as $t )
                <div class="col-md-3 col-lg-2">
                    <div class="bg-gray-100 border-radius-xl p-2 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <a href="{{ route('procedure_auditor.area.index', ['Name_Tractor' => $t->Name_Tractor]) }}">
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
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
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
