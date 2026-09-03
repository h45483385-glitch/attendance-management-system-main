@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Daily Attendance Sheet</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item">Attendance Records</li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Daily Sheet</li>
    </ol>
</div>
@endsection

@section('content')
<div class="card shadow-sm border-0 mt-2">
    <div class="card-body p-4">
        
        <!-- Header & Smart Search Box -->
        <div class="row mb-4 align-items-center">
            <!-- Dynamic Title -->
            <div class="col-md-6">
                <h4 class="mt-0 header-title font-weight-bold text-dark mb-0">
                    <i class="ti-calendar text-primary mr-2"></i> Today's Sheet: 
                    <span class="text-muted font-weight-normal" style="font-size: 15px;">({{ \Carbon\Carbon::now()->format('d M Y') }})</span>
                </h4>
            </div>
            
            <!-- Smart Search Bar (Directs to Full Logs) -->
            <div class="col-md-6 mt-3 mt-md-0">
                <!-- Form action directs to Full Logs page (/attendance) -->
                <form action="/attendance" method="GET" class="input-group" style="max-width: 350px; float: right;">
                    <input type="text" name="employee_query" class="form-control border-primary" placeholder="Search Emp ID or Name..." required>
                    <div class="input-group-append">
                        <button class="btn btn-primary px-3" type="submit">
                            <i class="ti-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vertical Table (Dynamic Data from DB) -->
        <div class="table-responsive mt-2">
            <table class="table table-hover table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Emp ID</th>
                        <th class="border-0">Employee Name</th>
                        <th class="border-0">Position</th>
                        <th class="border-0">Time In</th>
                        <th class="border-0">Time Out</th>
                        <th class="border-0 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- LOGIC: டேட்டாபேஸில் இருந்து வரும் இன்றைய Attendance-ஐ லூப் (Loop) செய்கிறது -->
                    @if(isset($dailyAttendances) && $dailyAttendances->count() > 0)
                        @foreach($dailyAttendances as $attendance)
                            <tr>
                                <td class="align-middle text-muted font-weight-bold">#{{ $attendance->employee_id ?? 'N/A' }}</td>
                                <td class="align-middle font-weight-bold text-dark">{{ $attendance->employee->name ?? 'N/A' }}</td>
                                <td class="align-middle text-muted">{{ $attendance->employee->position ?? 'N/A' }}</td>
                                
                                <td class="align-middle font-weight-medium">
                                    {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : '-' }}
                                </td>
                                <td class="align-middle {{ $attendance->time_out ? 'font-weight-medium' : 'text-muted font-italic' }}">
                                    {{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') : 'Still Working' }}
                                </td>
                                
                                <td class="align-middle text-center">
                                    @if($attendance->status == 'Present')
                                        <span class="badge badge-success px-3 py-1" style="font-size: 12px;">Present</span>
                                    @elseif($attendance->status == 'In Progress')
                                        <span class="badge badge-warning px-3 py-1 text-dark" style="font-size: 12px;">In Progress</span>
                                    @elseif($attendance->status == 'On Break' || $attendance->status == 'Break Time')
                                        <span class="badge px-3 py-1 text-white" style="background-color: #3b82f6; font-size: 12px;">On Break</span>
                                    @else
                                        <span class="badge badge-danger px-3 py-1" style="font-size: 12px;">Absent</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- LOGIC: இன்று யாரும் செக்கின் செய்யவில்லை என்றால் இதைக் காட்டும் -->
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti-info-alt text-warning mb-2" style="font-size: 24px; display: block;"></i>
                                No attendance records found for today yet.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection