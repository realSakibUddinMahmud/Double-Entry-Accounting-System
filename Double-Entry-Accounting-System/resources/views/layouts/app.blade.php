<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">
<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RyoFin is a robust inventory management system featuring double entry accounting and product transformation, designed to streamline business operations.">
    <meta name="keywords" content="RyoFin, inventory management, double entry accounting, product transformation, business management, responsive admin, POS system">
    <meta name="author" content="RyoFin Team">
    <meta name="robots" content="index, follow">
    <title>@yield('title', 'RyoFin') | {{ config('app.name', 'RyoFin') }}</title>

    <script src="{{ asset('assets/js/theme-script.js') }}" type="eb16dee38797852848cea039-text/javascript"></script>	

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS (static) -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- animation CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/@simonwep/pickr/themes/nano.min.css') }}">

    {{-- Optional theme CSS (if public/assets is available) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

</head>

<body>
    <!-- Main Wrapper -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="content w-100">
            @yield('content')
        </div>
    </div>
    @include('layouts.footer')
    <!-- /Main Wrapper -->

    <!-- Theme core scripts (static) -->
    <script src="{{ asset('assets/js/script.js') }}" type="text/javascript"></script>

</body>
</html>
