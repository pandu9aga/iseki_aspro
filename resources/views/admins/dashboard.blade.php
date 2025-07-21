@extends('layouts.admin')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-65 relative" style="background-image: url('{{ asset('assets/img/bg5.jpg') }}')">
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

    <section class="py-1">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 ms-auto me-auto p-lg-4 mt-lg-0 mt-4">
                    <div class="rotating-card-container">
                        <div class="card card-rotate card-background card-background-mask-primary shadow-dark mt-md-0 mt-5">
                            <div class="front front-background"
                                style="background-image: url('{{ asset('assets/img/rotate-1.jpg') }}'); background-size: cover;">
                                <div class="card-body py-7 text-center">
                                    <i class="material-symbols-rounded text-white text-4xl my-3">account_circle</i>
                                    <h3 class="text-white">Hi,<br/>{{ $user->Name_User }}</h3>
                                    <p class="text-white opacity-8">Welcome to Iseki Aspro (Assembling Procedure) website.</p>
                                </div>
                            </div>
                            <div class="back back-background"
                                style="background-image: url('{{ asset('assets/img/rotate-2.jpg') }}'); background-size: cover;">
                                <div class="card-body pt-7 text-center">
                                    <h3 class="text-white">Discover More</h3>
                                    <p class="text-white opacity-8">
                                        Check for some daily reports here.
                                    </p>
                                    <a href="#" class="btn btn-white btn-sm w-50 mx-auto mt-3">Check it out</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="col-lg-8 pt-3 pb-4" id="count-stats">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-9 mx-auto py-3">
                                <div class="row">
                                    <div class="col-md-4 position-relative">
                                        <div class="p-3 text-center">
                                            <h1 class="text-gradient text-primary"><span id="state1" countTo="{{ $tractors }}">0</span></h1>
                                            <h5 class="mt-3">Tractors</h5>
                                            <p class="text-sm font-weight-normal">
                                                Number of tractor types available on this website
                                            </p>
                                        </div>
                                        <hr class="vertical dark">
                                    </div>
                                    <div class="col-md-4 position-relative">
                                        <div class="p-3 text-center">
                                            <h1 class="text-gradient text-primary"><span id="state2" countTo="{{ $procedures }}">0</span></h1>
                                            <h5 class="mt-3">Procedures</h5>
                                            <p class="text-sm font-weight-normal">
                                                Total procedures for all existing tractor types
                                            </p>
                                        </div>
                                        <hr class="vertical dark">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 text-center">
                                            <h1 class="text-gradient text-primary"><span id="state3" countTo="{{ $users }}">0</span></h1>
                                            <h5 class="mt-3">Users</h5>
                                            <p class="text-sm font-weight-normal">
                                                Number of registered users on this website
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <!-- -------- START Content Presentation Docs ------- -->
    <div class="container mt-sm-5">
        <div class="page-header py-6 py-md-5 my-sm-3 mb-3 border-radius-xl"
            style="background-image: url('{{ asset('assets/img/ivancik.jpg') }}');"
            loading="lazy">
            <span class="mask bg-gradient-dark"></span>
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 ms-lg-5">
                        <h4 class="text-white">Manage</h4>
                        <h1 class="text-white">Procedure</h1>
                        <p class="lead text-white opacity-8">
                            Manage and organize existing assembling procedures in the procedures menu. 
                            You can upload procedure files (using pdf format), change them, delete them as per policy.
                        </p>
                        <a href="{{ route('procedure') }}"
                            class="text-white icon-move-right">
                            Read more
                            <i class="fas fa-arrow-right text-sm ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-6 mt-lg-0 mt-4">
                <div class="info-horizontal bg-gray-100 border-radius-xl d-block d-md-flex p-4 h-100">
                    <i class="material-symbols-rounded text-gradient text-primary text-3xl">groups</i>
                    <div class="ps-0 ps-md-3 mt-3 mt-md-0">
                        <h5>User</h5>
                        <p>
                            Manage and organize all registered users through the user management section. 
                            This feature allows administrators to add new users, edit existing information, assign user roles, and delete accounts when necessary.
                        </p>
                        <a href="{{ route('user') }}" class="text-primary icon-move-right">
                            Read more
                            <i class="fas fa-arrow-right text-sm ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-lg-0 mt-4">
                <div class="info-horizontal bg-gray-100 border-radius-xl d-block d-md-flex p-4">
                    <i class="material-symbols-rounded text-gradient text-primary text-3xl">receipt_long</i>
                    <div class="ps-0 ps-md-3 mt-3 mt-md-0">
                        <h5>Report</h5>
                        <p>
                             View and manage reports submitted by training members regarding the assembling procedures they have completed or reviewed. 
                             This section provides structured documentation of feedback, observations, and potential issues encountered during the training process.
                        </p>
                        <a href="#" class="text-primary icon-move-right">
                            Read more
                            <i class="fas fa-arrow-right text-sm ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- -------- END Content Presentation Docs ------- -->


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