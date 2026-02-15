@extends('layouts.admin-layout')

@section('content')
<div class="row">
    <!-- Invoices Awaiting Payment -->
    @include('components.stats-card')
    
    {{-- <!-- Payment Records -->
    @include('dashboard.partials.payment-records')
    
     <!-- Total Sales -->
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