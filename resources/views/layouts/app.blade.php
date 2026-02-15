<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PageTurner Bookstore')</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="{{ asset('booksaw/css/normalize.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('booksaw/icomoon/icomoon.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('booksaw/css/vendor.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{  asset('booksaw/style.css') }}" />
</head>

<body data-bs-spy="scroll" data-bs-target="#header" tabindex="0">
    <div class="min-h-screen">
        @include('partials.navigation')
        <!-- Page Heading -->
        @hasSection('header')
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6

                        lg:px-8">

                    @yield('header')
                </div>
            </header>
        @endif
        <!-- Flash Messages -->
        @include('partials.flash-messages')
        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
        @include('partials.footer')
    </div>

    @stack('scripts')
    
</body>

</html>