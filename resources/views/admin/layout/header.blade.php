<!doctype html>
<html lang="en" class="minimal-theme">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/admin/images/karate_logo.png') }}" type="image/png" />

  <!-- Plugins -->
  <link href="{{ asset('assets/admin/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />

  <!-- Bootstrap CSS -->
  <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/style1.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Loader -->
  <link href="{{ asset('assets/admin/css/pace.min.css') }}" rel="stylesheet" />

  <!-- Theme Styles -->
  <link href="{{ asset('assets/admin/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/light-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/header-colors.css') }}" rel="stylesheet" />

  <style>
    nav[role="navigation"] svg {
      width: 1.25rem !important;
      height: 1.25rem !important;
      max-width: 1.25rem !important;
      max-height: 1.25rem !important;
      display: inline-block !important;
      vertical-align: middle;
    }
    .pagination svg {
      width: 1rem !important;
      height: 1rem !important;
      display: inline-block !important;
    }
    .pagination {
      margin-bottom: 0;
    }
  </style>

  <title>Dashboard</title>
</head>

<body>

  <!--start wrapper-->
  <div class="wrapper">