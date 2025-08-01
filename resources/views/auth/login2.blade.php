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
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('{{ asset('assets/img/bg2.jpg') }}');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-5 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">

              <!-- Logo -->
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 text-center">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h3 class="text-white">Iseki Aspro</h3>
                  <h5 class="text-white" style="margin-top: -10px;">Assembling Procedure</h5>
                </div>
              </div>

              <!-- Tabs -->
              <section class="mt-4 py-1">
                <div class="container">
                  <div class="row">
                    <div class="mx-auto">
                        <div class="nav-wrapper position-relative end-0">
                          <ul class="nav nav-pills nav-fill p-1 bg-light rounded" role="tablist">
                            <li class="nav-item">
                              <a id="show-admin" class="nav-link mb-0 px-0 py-1 active fw-bold rounded" 
                                data-bs-toggle="tab" 
                                href="#profile-tabs-simple" 
                                role="tab" 
                                aria-controls="profile" 
                                aria-selected="true">
                                <span id="item-admin" class="text-primary">Admin</span>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a id="show-member" class="nav-link mb-0 px-0 py-1 fw-bold" 
                                data-bs-toggle="tab" 
                                href="#dashboard-tabs-simple" 
                                role="tab" 
                                aria-controls="dashboard" 
                                aria-selected="false">
                                <span id="item-member">Member</span>
                              </a>
                            </li>
                          </ul>
                        </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Forms wrapper -->
              <div class="card-body">
                @if ($errors->any())
                  <div class="text-danger mb-2">
                    @foreach ($errors->all() as $error)
                      <p>{{ $error }}</p>
                    @endforeach
                  </div>
                @endif

                <div class="position-relative" style="overflow: hidden; height: auto;">
                  <div class="form-slider d-flex transition-slide" style="width: 200%;">

                    <!-- Admin Form -->
                    <form id="admin-form" class="text-start w-100 px-2" action="{{ route('login.auth') }}" method="POST">
                      @csrf
                      <h5 class="text-primary">Login Admin</h5>
                      <div class="input-group input-group-outline my-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="Username_User" class="form-control">
                      </div>
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="Password_User" class="form-control">
                      </div>
                      <div class="text-center">
                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                      </div>
                    </form>

                    <!-- Member Form -->
                    <form id="member-form" class="text-start w-100 px-2" action="{{ route('login.member') }}" method="POST">
                      @csrf
                      <h5 class="text-primary">Login Member</h5>
                      <div class="input-group input-group-outline my-3">
                        <label class="form-label">NIK</label>
                        <input type="text" name="NIK_Member" class="form-control">
                      </div>
                      <div class="text-center">
                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                      </div>
                    </form>

                  </div>
                </div>
              </div> <!-- end card-body -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer position-absolute bottom-2 py-2 w-100 text-center">
    <div class="container">
      <span class="text-white text-sm">
        © <script>document.write(new Date().getFullYear())</script>,
        PT. Iseki Indonesia - Assembling Procedure
      </span>
    </div>
  </footer>

  <!-- JS (masih sama seperti milikmu) -->
  <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/material-dashboard.min.js?v=3.2.0') }}"></script>

  <style>
    .form-slider {
      transition: transform 0.5s ease-in-out;
    }
    .slide-left {
      transform: translateX(-50%);
    }
  </style>

  <script>
    const slider = document.querySelector('.form-slider');
    const showAdminBtn = document.getElementById('show-admin');
    const showMemberBtn = document.getElementById('show-member');
    const showAdminItem = document.getElementById('item-admin');
    const showMemberItem = document.getElementById('item-member');

    showAdminBtn.addEventListener('click', (e) => {
      e.preventDefault();
      slider.classList.remove('slide-left');

      showAdminItem.classList.add('text-primary');
      showAdminItem.classList.remove('text-dark');
      showMemberItem.classList.remove('text-primary');
      showMemberItem.classList.add('text-dark');
    });

    showMemberBtn.addEventListener('click', (e) => {
      e.preventDefault();
      slider.classList.add('slide-left');

      showMemberItem.classList.add('text-primary');
      showMemberItem.classList.remove('text-dark');
      showAdminItem.classList.remove('text-primary');
      showAdminItem.classList.add('text-dark');
    });
  </script>
</body>
</html>