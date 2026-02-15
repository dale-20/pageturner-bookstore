<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <title>@yield('title', 'Pageturner - Admin Dashboard')</title>
    
    <!-- Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('duralex/images/favicon.ico') }}" />
    
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('duralex/css/bootstrap.min.css') }}" />
    
    <!-- Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('duralex/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('duralex/vendors/css/daterangepicker.min.css') }}" />
    
    <!-- Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('duralex/css/theme.min.css') }}" />
    
    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    @include('partials.admin-navigation')

    <!-- Header -->
    @include('partials.admin-header')

     @include('partials.flash-messages')

    <!-- Main Content -->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- Page Header -->
            @include('partials.page-header')

            <!-- Main Content Area -->
            <div class="main-content">
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        @include('partials.admin-footer')
    </main>

    <!-- Theme Customizer -->
    @include('partials.theme-customizer')

    <!-- Scripts -->
    <script src="{{ asset('duralex/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/circle-progress.min.js') }}"></script>
    <script src="{{ asset('duralex/js/common-init.min.js') }}"></script>
    <script src="{{ asset('duralex/js/dashboard-init.min.js') }}"></script>
    <script src="{{ asset('duralex/js/theme-customizer-init.min.js') }}"></script>
    
    @stack('scripts')
</body>

</html>