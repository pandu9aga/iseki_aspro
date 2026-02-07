@extends('layouts.member')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-65 relative" style="background-image: url('{{ asset('assets/img/bg-dashboard.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-lg-7 text-center mx-auto">
                    <h1 class="text-white font-weight-black pt-3 mt-n5">Iseki Aspro</h1>
                    <p class="lead text-white mt-3">Assembling Procedure. <br />
                        Website for Assembling Procedure Training Process. </p>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <div class="container">
        <div class="row">
            <div class="col-lg-4 mt-lg-0 mt-4">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <div class="hover-card bg-gray-100 border-radius-xl p-4 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <i class="material-symbols-rounded text-primary" style="font-size: 100px;">home</i>
                        <br>
                        <div class="mt-3 mt-md-0">
                            <h4>Home</h4>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <a href="{{ route('report_member') }}" class="text-decoration-none">
                    <div class="hover-card bg-gray-100 border-radius-xl p-4 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <i class="material-symbols-rounded text-primary" style="font-size: 100px;">assignment</i>
                        <br>
                        <div class="mt-3 mt-md-0">
                            <h4>Training</h4>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <a href="{{ route('profile_member') }}" class="text-decoration-none">
                    <div class="hover-card bg-gray-100 border-radius-xl p-4 h-100 align-items-center d-flex flex-column justify-content-center shadow-lg">
                        <i class="material-symbols-rounded text-primary" style="font-size: 100px;">account_circle</i>
                        <br>
                        <div class="mt-3 mt-md-0">
                            <h4>Profile</h4>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <section class="py-1">
        <div class="container">
            <div class="row align-items-center">
                <section class="col-lg-8 pt-3 pb-4" id="count-stats">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-9 mx-auto py-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="p-3 text-center">
                                            <h1 class="text-gradient text-primary display-3"><b id="state3" countTo="{{ $reports }}">0</b></h1>
                                            <h3 class="mt-3 fw-bold">Training</h3>
                                            <p class="fs-5 fw-semibold">
                                                Number of your training process
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="col-lg-4 ms-auto me-auto p-lg-4 mt-lg-0 mt-4">
                    <div class="rotating-card-container">
                        <div class="card card-rotate card-background card-background-mask-primary shadow-dark mt-md-0 mt-5">
                            <div class="front front-background"
                                style="background-image: url('{{ asset('assets/img/rotate-1.jpg') }}'); background-size: cover;">
                                <div class="card-body py-7 text-center">
                                    <i class="material-symbols-rounded text-white text-4xl my-3">account_circle</i>
                                    <h3 class="text-white">Hi,<br/>{{ $member->Name_Member }}</h3>
                                    <p class="text-white opacity-8">Welcome to Iseki Aspro (Assembling Procedure) website.</p>
                                </div>
                            </div>
                            <div class="back back-background"
                                style="background-image: url('{{ asset('assets/img/rotate-2.jpg') }}'); background-size: cover;">
                                <div class="card-body pt-7 text-center">
                                    <h3 class="text-white">Discover More</h3>
                                    <p class="text-white opacity-8">
                                        Check for your training here.
                                    </p>
                                    <a href="{{ route('report_member') }}" class="btn btn-white btn-sm w-50 mx-auto mt-3">Check it out</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-3 pb-5">
        <div class="container">
            <hr class="horizontal dark my-5">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-6 mx-auto">
                    <img class="w-100 opacity-6" src="{{ asset('assets/img/logo-iseki-grey.png') }}" alt="Logo">
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@section('style')
<style>
    .hover-card {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .hover-card:hover {
        background-color: #e91e63 !important; /* Biru Bootstrap */
        color: white !important;
        transform: translateY(-5px);
    }

    .hover-card:hover i,
    .hover-card:hover h4 {
        color: white !important;
    }
</style>
@endsection

@section('script')
<script src="{{ asset('assets/js/plugins/countup.min.js') }}"></script>
<script type="text/javascript">
    if (document.getElementById('state1')) {
        const countUp = new CountUp('state1', document.getElementById("state1").getAttribute("countTo"));
        if (!countUp.error) {
            countUp.start();
        } else {
            console.error(countUp.error);
        }
    }
    if (document.getElementById('state2')) {
        const countUp1 = new CountUp('state2', document.getElementById("state2").getAttribute("countTo"));
        if (!countUp1.error) {
            countUp1.start();
        } else {
            console.error(countUp1.error);
        }
    }
    if (document.getElementById('state3')) {
        const countUp2 = new CountUp('state3', document.getElementById("state3").getAttribute("countTo"));
        if (!countUp2.error) {
            countUp2.start();
        } else {
            console.error(countUp2.error);
        };
    }
</script>
@endsection

