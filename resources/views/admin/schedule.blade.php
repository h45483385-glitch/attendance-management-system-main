@extends('layouts.master')

@section('css')
<style>
    .shift-badge { 
        font-weight: 600; 
        padding: 5px 12px; 
        border-radius: 20px; 
        font-size: 11px; 
        display: inline-flex; 
        align-items: center; 
        gap: 4px;
    }
    .shift-morning { background-color: #e0f2fe; color: #0369a1; }
    .shift-mid { background-color: #dcfce7; color: #166534; }
    .shift-night { background-color: #f1f5f9; color: #475569; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Work Schedules</h4>
    <p class="text-secondary font-13 mb-0">Configure operational hour boundaries, shift durations, and roster expectations.</p>
</div>
@endsection

@section('content')
@include('includes.flash')

<div class="row mt-3">
    <div class="col-12">
        <div class="card saas-card">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-time text-primary mr-2"></i> Operational Shifts</h5>
                <button class="btn btn-saas btn-saas-primary px-4 shadow-sm" data-toggle="modal" data-target="#addShiftModal">
                    <i class="ti-plus mr-1"></i> Add Shift
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">ID</th>
                                <th>Shift Category</th>
                                <th class="text-center">Expected Hours</th>
                                <th class="text-center">Start Time</th>
                                <th class="text-center">End Time</th>
                                <th class="text-right" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $shift)
                            @php
                                $start = \Carbon\Carbon::parse($shift->time_in);
                                $end = \Carbon\Carbon::parse($shift->time_out);
                                if ($end->lessThan($start)) {
                                    $end->addDay();
                                }
                                $totalHours = round($start->diffInMinutes($end) / 60, 1);
                                
                                $badgeClass = 'shift-night';
                                if($loop->iteration == 1) $badgeClass = 'shift-morning';
                                elseif($loop->iteration == 2) $badgeClass = 'shift-mid';
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted">#{{ $shift->id }}</td>
                                <td>
                                    <span class="shift-badge {{ $badgeClass }}">
                                        {{ $shift->slug }}
                                    </span>
                                </td>
                                <td class="text-center font-weight-bold text-primary">{{ $totalHours }} Hours</td>
                                <td class="text-center"><i class="ti-time text-muted mr-1"></i> {{ \Carbon\Carbon::parse($shift->time_in)->format('h:i A') }}</td>
                                <td class="text-center"><i class="ti-time text-muted mr-1"></i> {{ \Carbon\Carbon::parse($shift->time_out)->format('h:i A') }}</td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="action-icon-btn" title="Edit" data-toggle="modal" data-target="#editShift{{ $shift->id }}"><i class="ti-pencil"></i></button>
                                        <button class="action-icon-btn text-danger" title="Delete" data-toggle="modal" data-target="#deleteShift{{ $shift->id }}"><i class="ti-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No custom work shifts configured yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     MODALS SECTION
=============================================== -->

<!-- 1. Add New Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-plus text-primary mr-2"></i> Register Shift Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/schedule') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Shift Category Name</label>
                        <input type="text" name="slug" class="form-control form-control-saas" placeholder="e.g. General Day Shift" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted font-12 mb-1">Shift Start Time</label>
                            <input type="time" name="start_time" class="form-control form-control-saas" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted font-12 mb-1">Shift End Time</label>
                            <input type="time" name="end_time" class="form-control form-control-saas" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loop for Edit & Delete Modals -->
@foreach($schedules as $shift)
<!-- Edit Modal -->
<div class="modal fade" id="editShift{{ $shift->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Modify Shift: {{ $shift->slug }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/schedule/'.$shift->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Shift Category Name</label>
                        <input type="text" name="slug" class="form-control form-control-saas" value="{{ $shift->slug }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted font-12 mb-1">Shift Start Time</label>
                            <input type="time" name="start_time" class="form-control form-control-saas" value="{{ \Carbon\Carbon::parse($shift->time_in)->format('H:i') }}" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted font-12 mb-1">Shift End Time</label>
                            <input type="time" name="end_time" class="form-control form-control-saas" value="{{ \Carbon\Carbon::parse($shift->time_out)->format('H:i') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4">Update Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteShift{{ $shift->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-white"><i class="ti-alert mr-2"></i> Confirm Delete</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/schedule/'.$shift->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center py-4 p-4">
                    <i class="ti-trash text-danger" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
                    <h5 class="font-weight-bold">Are you sure you want to delete shift?</h5>
                    <p class="text-muted mb-0">Shift type: <b>"{{ $shift->slug }}"</b>. This action cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4 justify-content-between">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-danger px-4">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection