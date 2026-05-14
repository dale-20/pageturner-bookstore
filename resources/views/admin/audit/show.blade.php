@extends('layouts.admin-layout')

@section('title', 'Audit Log Detail')

@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1">{{ $auditLog->event }} on {{ class_basename($auditLog->auditable_type) }}</h5>
                    <div class="text-muted">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</div>
                </div>
                <span class="badge {{ $isVerified ? 'bg-success' : 'bg-danger' }}">
                    {{ $isVerified ? 'Checksum Verified' : 'Checksum Failed' }}
                </span>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-3"><strong>User:</strong> {{ $auditLog->user?->email ?? 'System' }}</div>
                <div class="col-md-3"><strong>IP:</strong> {{ $auditLog->ip_address }}</div>
                <div class="col-md-3"><strong>Method:</strong> {{ $auditLog->method }}</div>
                <div class="col-md-3"><strong>Model ID:</strong> {{ $auditLog->auditable_id }}</div>
                <div class="col-12"><strong>URL:</strong> {{ $auditLog->url }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($diff as $field => $values)
                        <tr class="{{ $values['changed'] ? 'table-warning' : '' }}">
                            <td>{{ $field }}</td>
                            <td><code>{{ json_encode($values['old']) }}</code></td>
                            <td><code>{{ json_encode($values['new']) }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No field changes were recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <a href="{{ route('admin.audit.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>
@endsection
