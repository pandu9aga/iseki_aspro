<!--
=========================================================
* Material Dashboard 3 - v3.2.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-dashboard
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{asset('assets/img/apple-icon.png')}}">
  <link rel="icon" type="image/png" href="{{asset('assets/img/favicon.png')}}">
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
</head>

<body class="bg-gray-200">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg blur border-radius-xl top-0 z-index-3 shadow position-absolute my-3 py-2 start-0 end-0 mx-4">
          <div class="container-fluid ps-2 pe-0">
            <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 " href="{{ route('/') }}">
              <span class="text-primary">Iseki Aspro</span> <small>- Assembling Procedure</small>
            </a>
          </div>
        </nav>
        <!-- End Navbar -->
      </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('{{asset('assets/img/bg2.jpg')}}');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-dark border-radius-lg py-3 pe-1">
                  <img src="{{asset('assets/img/logo-iseki-white.png')}}" alt="main_logo" class="img-fluid d-block mx-auto" style="max-height: 40px;">
                </div>
              </div>
              <div class="card-body">
                @if ($errors->any())
                  <div>
                      @foreach ($errors->all() as $error)
                        <p style="color:red;">{{ $error }}</p>
                      @endforeach
                  </div>
                @endif
                <form role="form" class="text-start" action="{{ route('login.auth') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control" name="NIK_User" value="">
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="Password_User" value="">
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-center">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-center">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                PT. Iseki Indonesia - Assembling Procedure
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <!--   Core JS Files   -->
  <script src="{{asset('assets/js/core/popper.min.js')}}"></script>
  <script src="{{asset('assets/js/core/bootstrap.min.js')}}"></script>
  <script src="{{asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
  <script src="{{asset('assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{asset('assets/js/material-dashboard.min.js?v=3.2.0')}}"></script>
</body>

</html>