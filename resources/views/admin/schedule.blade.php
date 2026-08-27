@extends('layouts.master')

@section('content')

<style>
    .table-schedule th {
        background-color: #f8fafc; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;
    }
    .table-schedule td { vertical-align: middle; color: #1e293b; }
    
    /* STRICT COLOR PALETTE: Blue, Green, White */
    .shift-badge { font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; display: inline-block; }
    .shift-morning { background-color: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; }
    .shift-mid { background-color: #dcfce7; color: #059669; border: 1px solid #a7f3d0; }
    .shift-night { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
</style>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-body p-4">
        
        <!-- Header Section -->
        <div class="mb-4">
            <h2 class="mt-0 font-weight-bold text-dark mb-0" style="font-size: 28px;">
                <i class="ti-time text-primary mr-2"></i> Schedules Management
            </h2>
        </div>

        <!-- Schedules Table -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 table-schedule">
                <thead>
                    <tr>
                        <th class="text-center" width="8%">ID</th>
                        <th>Shift Category Name</th>
                        <th class="text-center">Total Hours</th>
                        <th class="text-center">Expected Start Time</th>
                        <th class="text-center">Expected End Time</th>
                        <th class="text-center" width="12%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($schedules) && count($schedules) > 0)
                        @foreach($schedules as $shift)
                            @php
                                // Dynamic Calculation of Total Hours (Handles overnight shifts too)
                                $start = \Carbon\Carbon::parse($shift->time_in);
                                $end = \Carbon\Carbon::parse($shift->time_out);
                                if ($end->lessThan($start)) {
                                    $end->addDay();
                                }
                                $totalHours = round($start->diffInMinutes($end) / 60, 1);
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted">#{{ $shift->id }}</td>
                                <td class="font-weight-bold">
                                    <span class="shift-badge 
                                        {{ $loop->iteration == 1 ? 'shift-morning' : ($loop->iteration == 2 ? 'shift-mid' : 'shift-night') }}">
                                        {{ $shift->slug }}
                                    </span>
                                </td>
                                <td class="text-center font-weight-bold text-primary">{{ $totalHours }} Hours</td>
                                <td class="text-center"><i class="ti-time text-muted mr-1"></i> {{ \Carbon\Carbon::parse($shift->time_in)->format('h:i A') }}</td>
                                <td class="text-center"><i class="ti-time text-muted mr-1"></i> {{ \Carbon\Carbon::parse($shift->time_out)->format('h:i A') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary mr-1" title="Edit Shift" data-toggle="modal" data-bs-toggle="modal" data-target="#editShift{{ $shift->id }}" data-bs-target="#editShift{{ $shift->id }}">
                                        <i class="ti-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Shift" data-toggle="modal" data-bs-toggle="modal" data-target="#deleteShift{{ $shift->id }}" data-bs-target="#deleteShift{{ $shift->id }}">
                                        <i class="ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No data available in table</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Add Shift Button -->
        <div class="text-right mt-4">
            <button class="btn btn-outline-primary font-weight-bold shadow-sm px-4" data-toggle="modal" data-bs-toggle="modal" data-target="#addShiftModal" data-bs-target="#addShiftModal">
                <i class="ti-plus mr-1"></i> Add New Shift
            </button>
        </div>
        
    </div>
</div>

<!-- ==============================================
                MODALS SECTION 
=============================================== -->

<!-- 1. Add New Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-plus text-primary mr-1"></i> Add New Shift</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/schedule') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Shift Category Name</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g., General Shift" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loop for Edit & Delete Modals -->
@if(isset($schedules))
    @foreach($schedules as $shift)
        <!-- Edit Modal -->
        <div class="modal fade" id="editShift{{ $shift->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-1"></i> Edit Shift</h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ url('/schedule/'.$shift->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Shift Category Name</label>
                                <input type="text" name="slug" class="form-control" value="{{ $shift->slug }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Start Time</label>
                                    <input type="time" name="start_time" class="form-control" value="{{ \Carbon\Carbon::parse($shift->time_in)->format('H:i') }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>End Time</label>
                                    <input type="time" name="end_time" class="form-control" value="{{ \Carbon\Carbon::parse($shift->time_out)->format('H:i') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Shift</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal fade" id="deleteShift{{ $shift->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title font-weight-bold text-white"><i class="ti-alert mr-1"></i> Confirm Delete</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ url('/schedule/'.$shift->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body text-center py-4">
                                <i class="ti-trash text-danger" style="font-size: 50px;"></i>
                                <h5 class="mt-3">Are you sure you want to delete <br><b>"{{ $shift->slug }}"</b>?</h5>
                                <p class="text-muted">This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer bg-light justify-content-center">
                                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger px-4">Yes, Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

@endsection