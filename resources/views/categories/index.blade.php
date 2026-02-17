@extends('layouts.admin-layout')

@section('title', 'Admin Categories - PageTurner')
@section('page-title', 'Categories')
@section('breadcrumb', 'Categories')


@section('add-features')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="feather-plus me-2"></i>
        <span>Create Category</span>
    </a>
@endsection


@section('content')
    <!-- Leads Table -->
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="leadList">
                        <thead>
                            <tr>
                                <th class="wd-30">
                                    <div class="btn-group mb-1">
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAllLead">
                                            <label class="custom-control-label" for="checkAllLead"></label>
                                        </div>
                                    </div>
                                </th>
                                {{-- <th>Book ID</th> --}}
                                <th>Name</th>
                                <th>Description</th>
                                {{-- <th>ISBN</th>
                                <th>Price</th>
                                <th>Stock</th> --}}
                                {{-- <th>Source</th>
                                <th>Phone</th> --}}
                                <th>Date Added</th>
                                <th>Actions</th>
                                {{-- <th>Status</th>
                                <th class="text-end">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="single-item">
                                    <td>
                                        <div class="item-checkbox ms-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkbox"
                                                    id="checkBox_{{ $category->id }}">
                                                <label class="custom-control-label" for="checkBox_{{ $category->id }}"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{-- <a href="{{ route('admin.categories.show', $category) }}" class="hstack gap-3">
                                            --}}
                                            {{-- @if($lead->avatar) --}}
                                            {{-- <div class="avatar-image avatar-md">
                                                <img src="{{ asset('images/book_images/book-placeholder.png') }}"
                                                    alt="user-image" class="img-fluid">
                                            </div> --}}
                                            {{-- @else
                                            <div class="avatar-image avatar-md bg-{{ $lead->avatar_color }} text-white">
                                                {{ substr($lead->name, 0, 1) }}
                                            </div>
                                            @endif --}}
                                            <div>
                                                <span class="text-truncate-1-line">{{ $category->name }}</span>
                                            </div>
                                        </a>
                                    </td>
                                    {{-- <td>{{ $category->id }}</td> --}}
                                    {{-- <td>{{ $category->title }}</td> --}}
                                    <td>{{ $category->description }}</td>
                                    {{-- <td>{{ $category->isbn }}</td>
                                    <td>{{ $category->price }}</td>
                                    <td>{{ $category->stock_quantity }}</td> --}}
                                    {{-- <td>
                                        <div class="hstack gap-2">
                                            <div class="avatar-text avatar-sm">
                                                <i class="feather-{{ $lead->source_icon }}"></i>
                                            </div>
                                            <a href="javascript:void(0);">{{ $lead->source }}</a>
                                        </div>
                                    </td>
                                    <td><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></td> --}}
                                    <td>{{ $category->created_at->format('Y-m-d, h:iA') }}</td>

                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('admin.categories.show', $category->id) }}"
                                                class="avatar-text avatar-md">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="avatar-text avatar-md">
                                                <i class="feather feather-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="avatar-text avatar-md border-0 bg-transparent"
                                                    style="cursor: pointer;">
                                                    <i class="feather feather-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    {{-- <td>
                                        <select class="form-control status-select" data-lead-id="{{ $lead->id }}"
                                            data-select2-selector="status">
                                            <option value="primary" data-bg="bg-primary" {{ $lead->status == 'new' ? 'selected'
                                                : '' }}>New</option>
                                            <option value="warning" data-bg="bg-warning" {{ $lead->status == 'working' ?
                                                'selected' : '' }}>Working</option>
                                            <option value="success" data-bg="bg-success" {{ $lead->status == 'qualified' ?
                                                'selected' : '' }}>Qualified</option>
                                            <option value="danger" data-bg="bg-danger" {{ $lead->status == 'declined' ?
                                                'selected' : '' }}>Declined</option>
                                            <option value="teal" data-bg="bg-teal" {{ $lead->status == 'customer' ? 'selected' :
                                                '' }}>Customer</option>
                                            <option value="indigo" data-bg="bg-indigo" {{ $lead->status == 'contacted' ?
                                                'selected' : '' }}>Contacted</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="avatar-text avatar-md">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" class="avatar-text avatar-md"
                                                    data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                    <i class="feather feather-more-horizontal"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('leads.edit', $lead->id) }}">
                                                            <i class="feather feather-edit-3 me-3"></i>
                                                            <span>Edit</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item printBTN" href="javascript:void(0)"
                                                            onclick="window.print()">
                                                            <i class="feather feather-printer me-3"></i>
                                                            <span>Print</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="setReminder({{ $lead->id }})">
                                                            <i class="feather feather-clock me-3"></i>
                                                            <span>Remind</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="archiveLead({{ $lead->id }})">
                                                            <i class="feather feather-archive me-3"></i>
                                                            <span>Archive</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="reportSpam({{ $lead->id }})">
                                                            <i class="feather feather-alert-octagon me-3"></i>
                                                            <span>Report Spam</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                            onclick="deleteLead({{ $lead->id }})">
                                                            <i class="feather feather-trash-2 me-3"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

{{--
<script src="{{  asset('duralex/vendors/js/vendors.min.js') }}"></script> --}}
<!-- vendors.min.js {always must need to be top} -->
@section('scripts')
    <script src="{{ asset('duralex/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/select2.min.js') }}"></script>
    <script src="{{  asset('duralex/vendors/js/select2-active.min.js') }}"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    {{--
    <script src="{{ asset('duralex/js/common-init.min.js') }}"></script> --}}
    {{--
    <script src="{{ asset('duralex/js/leads-init.min.js') }}"></script> --}}
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    {{--
    <script src="{{  asset('duralex/js/theme-customizer-init.min.js') }}"></script> --}}
@endsection