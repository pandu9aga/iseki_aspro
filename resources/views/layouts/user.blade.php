<!--
=========================================================
* Material Kit 3 - v3.1.0
=========================================================

* Product Page:  https://www.creative-tim.com/product/material-kit 
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Coded by www.creative-tim.com

 =========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>
        Iseki Aspro - Assembling Procedure
    </title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/style.css')}}" />
    <!-- Nucleo Icons -->
    <link href="{{asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="{{asset('assets/js/42d5adcbca.js')}}" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet" href="{{asset('assets/css/icon.css')}}" />
    <!-- CSS Files -->
    <link id="pagestyle" href="{{asset('assets/css/material-dashboard.css?v=3.2.0')}}" rel="stylesheet" />

    @yield('style')
</head>

<body class="index-page bg-gray-200">
    <!-- Navbar -->
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <nav
                    class="navbar navbar-expand-lg  blur border-radius-xl top-0 z-index-fixed shadow position-absolute my-3 p-2 start-0 end-0 mx-4">
                    <div class="container-fluid px-0">
                        <a class="navbar-brand font-weight-bolder ms-sm-3 text-sm"
                            href="{{ route('home') }}" rel="tooltip"
                            title="Designed and Coded by Creative Tim" data-placement="bottom">
                            <span class="text-primary">Iseki Aspro</span> <small>- Assembling Procedure</small>
                        </a>
                        <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon mt-2">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </span>
                        </button>
                        <div class="collapse navbar-collapse pt-3 pb-2 py-lg-0 w-100" id="navigation">
                            <ul class="navbar-nav navbar-nav-hover ms-auto">
                                <li class="nav-item dropdown dropdown-hover mx-2">
                                    <a class="nav-link ps-2 d-flex cursor-pointer align-items-center font-weight-semibold {{ $page === 'home' ? 'text-primary' : '' }}"
                                        href="{{ route('home') }}">
                                        <i class="material-symbols-rounded opacity-6 me-2 text-md">home</i>
                                        Home
                                    </a>
                                </li>
                                <li class="nav-item dropdown dropdown-hover mx-2">
                                    <a class="nav-link ps-2 d-flex cursor-pointer align-items-center font-weight-semibold {{ $page === 'report' ? 'text-primary' : '' }}""
                                        href="{{ route('report_user') }}">
                                        <i class="material-symbols-rounded opacity-6 me-2 text-md">assignment</i>
                                        Report
                                    </a>
                                </li>
                                <li class="nav-item dropdown dropdown-hover mx-2">
                                    <a class="nav-link ps-2 d-flex cursor-pointer align-items-center font-weight-semibold {{ $page === 'profile' ? 'text-primary' : '' }}""
                                        href="{{ route('profile_user') }}">
                                        <i class="material-symbols-rounded opacity-6 me-2 text-md">account_circle</i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item my-auto ms-3 ms-lg-0">
                                    <a href="{{ route('logout') }}"
                                        class="btn  bg-gradient-primary  mb-0 mt-2 mt-md-0">
                                        <i class="material-symbols-rounded opacity-6 me-2 text-md">logout</i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
            </div>
        </div>
    </div>

    @yield('content')

    <footer class="footer pt-5 mt-3">
        <div class="container">
            <div class=" row">
                <div class="col-md-3 mb-4 ms-auto">
                    <div>
                        <h4 class="font-weight-bolder text-primary mb-0">Iseki Aspro</h4>
                        <h5 class="mb-4">Assembling Procedure</h5>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-4">
                    <div>
                        <h6 class="text-sm text-primary">Menu</h6>
                        <ul class="flex-column ms-n3 nav">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">
                                    Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report_user') }}">
                                    Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile_user') }}">
                                    Profile
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6 col-6 mb-4">
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-4">
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-4">
                </div>

                <div class="col-12">
                    <div class="text-center">
                        <p class="text-dark my-4 text-sm font-weight-normal">
                            © <script>
                                document.write(new Date().getFullYear())
                            </script>,
                             <span class="text-primary">PT. Iseki Indonesia</span> - Assembling Procedure
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!--   Core JS Files   -->
    <script src="{{asset('assets/js/core/popper.min.js')}}"></script>
    <script src="{{asset('assets/js/core/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('assets/js/material-dashboard.min.js?v=3.2.0')}}"></script>
    <!--  Plugin for TypedJS, full documentation here: https://github.com/inorganik/CountUp.js -->
    {{-- <script src="./assets/js/plugins/choices.min.js"></script>
    <script src="./assets/js/plugins/prism.min.js"></script>
    <script src="./assets/js/plugins/highlight.min.js"></script> --}}
    <!--  Plugin for Parallax, full documentation here: https://github.com/dixonandmoe/rellax -->
    {{-- <script src="./assets/js/plugins/rellax.min.js"></script> --}}
    <!--  Plugin for TiltJS, full documentation here: https://gijsroge.github.io/tilt.js/ -->
    {{-- <script src="./assets/js/plugins/tilt.min.js"></script> --}}
    <!--  Plugin for Selectpicker - ChoicesJS, full documentation here: https://github.com/jshjohnson/Choices -->
    {{-- <script src="./assets/js/plugins/choices.min.js"></script> --}}
    <!-- Control Center for Material UI Kit: parallax effects, scripts for the example pages etc -->
    {{-- <script src="./assets/js/material-kit.min.js?v=3.1.0" type="text/javascript"></script> --}}

    @yield('script')
</body>

</html>
