@extends('layouts.admin-layout')

@section('content')
    <div class="row g-4">

        {{-- Stats Cards --}}
        @include('components.stats-card')

        {{-- Order Status Summary --}}
        @include('components.order-status-summary')

        {{-- Recent Orders --}}
        @include('components.recent-orders')

        {{-- Recent Reviews --}}
        @include('components.recent-reviews')

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('duralex/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/select2-active.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Use unique IDs (dashboardOrderList, dashboardReviewList) that
            // common-init.min.js won't auto-initialize
            $('#dashboardOrderList').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                info: true,
                lengthChange: true,
                order: [[5, 'desc']]
            });

            $('#dashboardReviewList').DataTable({
                pageLength: 8,
                ordering: true,
                searching: true,
                info: true,
                lengthChange: false,
                order: [[4, 'desc']]
            });

            // Tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
@endsection