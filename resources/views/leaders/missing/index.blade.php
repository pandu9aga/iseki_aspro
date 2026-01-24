@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Missing PIC Procedures</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('missing') }}">Missing PIC</a></li>
        </ol>
    </nav>

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    {{ session('success') }}
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

            @if($tractors->isEmpty())
                <div class="alert alert-info text-white">
                    <i class="material-symbols-rounded">info</i>
                    Tidak ada procedure tanpa PIC
                </div>
            @else
                <div class="row mt-4">
                    @foreach ( $tractors as $t )
                    <div class="col-md-3 col-lg-2">
                        <div class="bg-gray-100 border-radius-xl p-2 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                            <a href="{{ route('missing.area.index', ['Name_Tractor' => $t->Name_Tractor]) }}">
                                <div class="hover-card bg-white border-radius-xl align-items-center d-flex flex-column justify-content-center w-100 p-1 shadow-lg">
                                    <div style="width: 180px; height: 180px; display: flex; align-items: center; justify-content: center;">
                                        <img src="{{ asset($t->Photo_Tractor ?? 'storage/tractors/default.png') }}"
                                            alt="{{ $t->Name_Tractor }}"
                                            style="max-width: 180px; max-height: 180px; width: auto; height: auto;">
                                    </div>
                                    <b class="text-primary">{{ $t->Name_Tractor }}</b>
                                    
                                    <div>
                                        <span class="badge bg-warning">
                                            {{ $tractorProcedureCounts[$t->Name_Tractor] ?? 0 }} Missing PIC
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
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
        background-color: #ff9800 !important;
        color: white !important;
        transform: translateY(-5px);
    }

    .hover-card:hover b {
        color: white !important;
    }

    .hover-card:hover .badge {
        background: linear-gradient(310deg, #ffffff 0%, #f0f0f0 100%) !important;
        color: #ff9800 !important;
    }
</style>
@endsection