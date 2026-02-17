@extends('layouts.admin-layout')

@section('content')
    <div class="row">
        <!-- Invoices Awaiting Payment -->
        @include('components.stats-card')

        <!-- Payment Records -->
        @include('components.recent-orders')

        {{-- <!-- Total Sales -->
        @include('dashboard.partials.total-sales')

        <!-- Mini Stats -->
        @include('dashboard.partials.mini-stats')

        <!-- Leads Overview -->
        @include('dashboard.partials.leads-overview')

        <!-- Latest Leads -->
        @include('dashboard.partials.latest-leads')

        <!-- Upcoming Schedule -->
        @include('dashboard.partials.upcoming-schedule')

        <!-- Project Status -->
        @include('dashboard.partials.project-status')

        <!-- Team Progress -->
        @include('dashboard.partials.team-progress')
        --}}
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('duralex/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/select2.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/select2-active.min.js') }}"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    {{--
    <script src="{{ asset('duralex/js/common-init.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('duralex/js/leads-init.min.js') }}"></script> --}}
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    {{--
    <script src="{{  asset('duralex/js/theme-customizer-init.min.js') }}"></script> --}}
@endsection