<!doctype html>
<html lang="en" class="minimal-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/admin/images/karate_logo.png') }}" type="image/png" />

  <!-- CSS Assets -->
  <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/style1.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/pace.min.css') }}" rel="stylesheet" />

  <title>Admin Login</title>
</head>

<body>
  <div class="wrapper">
    <main class="authentication-content">
      <div class="container-fluid">
        <div class="authentication-card">
          <div class="card shadow rounded-0 overflow-hidden">
            <div class="row g-0">
              <div class="col-lg-6 bg-login d-flex align-items-center justify-content-center">
                <img src="{{ asset('assets/admin/images/error/login-img.jpg') }}" class="img-fluid" alt="login image">
              </div>
              <div class="col-lg-6">
                <div class="card-body p-4 p-sm-5">
                  <h5 class="card-title">Admin Login</h5>
                  <p class="card-text mb-4">Sign in to access your dashboard</p>

                  @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                  @endif

                  <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf
                    <div class="row g-3">
                      <div class="col-12">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="position-relative">
                          <div class="position-absolute top-50 translate-middle-y search-icon px-3">
                            <i class="bi bi-envelope-fill"></i>
                          </div>
                          <input type="email" name="email" id="email" class="form-control radius-30 ps-5" placeholder="Enter Email" required>
                        </div>
                      </div>
                      <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <div class="position-relative">
                          <div class="position-absolute top-50 translate-middle-y search-icon px-3">
                            <i class="bi bi-lock-fill"></i>
                          </div>
                          <input type="password" name="password" id="password" class="form-control radius-30 ps-5" placeholder="Enter Password" required>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="d-grid">
                          <button type="submit" class="btn btn-primary radius-30">Login</button>
                        </div>
                      </div>
                      <div class="col-12 text-center">
                        <p class="mb-0">Don't have access? Contact admin.</p>
                      </div>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- JS Assets -->
  <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/pace.min.js') }}"></script>
</body>
</html>
