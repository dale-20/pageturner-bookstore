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
                            <th>Customer</th>
                            <th>Email</th>
                            {{-- <th>Source</th>
                            <th>Phone</th> --}}
                            <th>Date</th>
                            {{-- <th>Status</th>
                            <th class="text-end">Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        @php
                            $orderItem = $order->orderItems[0]
                        @endphp
                        <tr class="single-item">
                            <td>
                                <div class="item-checkbox ms-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input checkbox" id="checkBox_{{ $order->id }}">
                                        <label class="custom-control-label" for="checkBox_{{ $order->id }}"></label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="" class="hstack gap-3">
                                    {{-- @if($lead->avatar) --}}
                                        <div class="avatar-image avatar-md">
                                            <img src="{{ asset('duralex/images/profile_default.png') }}" alt="user-image" class="img-fluid">
                                        </div>
                                    {{-- @else
                                        <div class="avatar-image avatar-md bg-{{ $lead->avatar_color }} text-white">
                                            {{ substr($lead->name, 0, 1) }}
                                        </div>
                                    @endif --}}
                                    <div>
                                        <span class="text-truncate-1-line">{{ $order->user->name }}</span>
                                    </div>
                                </a>
                            </td>
                            <td>{{ $order->user->email }}</td>
                            {{-- <td>
                                <div class="hstack gap-2">
                                    <div class="avatar-text avatar-sm">
                                        <i class="feather-{{ $lead->source_icon }}"></i>
                                    </div>
                                    <a href="javascript:void(0);">{{ $lead->source }}</a>
                                </div>
                            </td>
                            <td><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></td> --}}
                            <td>{{ $order->created_at->format('Y-m-d, h:iA') }}</td>
                            {{-- <td>
                                <select class="form-control status-select" data-lead-id="{{ $lead->id }}" data-select2-selector="status">
                                    <option value="primary" data-bg="bg-primary" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="warning" data-bg="bg-warning" {{ $lead->status == 'working' ? 'selected' : '' }}>Working</option>
                                    <option value="success" data-bg="bg-success" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                    <option value="danger" data-bg="bg-danger" {{ $lead->status == 'declined' ? 'selected' : '' }}>Declined</option>
                                    <option value="teal" data-bg="bg-teal" {{ $lead->status == 'customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="indigo" data-bg="bg-indigo" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                </select>
                            </td>
                            <td>
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="avatar-text avatar-md">
                                        <i class="feather feather-eye"></i>
                                    </a>
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
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
                                                <a class="dropdown-item printBTN" href="javascript:void(0)" onclick="window.print()">
                                                    <i class="feather feather-printer me-3"></i>
                                                    <span>Print</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="setReminder({{ $lead->id }})">
                                                    <i class="feather feather-clock me-3"></i>
                                                    <span>Remind</span>
                                                </a>
                                            </li>
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="archiveLead({{ $lead->id }})">
                                                    <i class="feather feather-archive me-3"></i>
                                                    <span>Archive</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="reportSpam({{ $lead->id }})">
                                                    <i class="feather feather-alert-octagon me-3"></i>
                                                    <span>Report Spam</span>
                                                </a>
                                            </li>
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteLead({{ $lead->id }})">
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