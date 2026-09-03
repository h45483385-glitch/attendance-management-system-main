@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Leave Logs</h4>
    <p class="text-secondary font-13 mb-0">Monitor approved leaves, timestamps, and check-in variances.</p>
</div>
@endsection

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="card saas-card">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-calendar text-primary mr-2"></i> Leave History</h5>
                <a href="/leave/assign" class="btn btn-saas btn-saas-primary px-4"><i class="ti-plus mr-1"></i> Register Leave</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee Details</th>
                                <th>Leave Logged Time</th>
                                <th>Schedule Hours</th>
                                <th class="text-right">Shift Match</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $leave->leave_date }}</span>
                                </td>
                                <td>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $leave->employee->name ?? 'N/A' }}</h6>
                                    <span class="text-muted font-11">ID: #{{ $leave->emp_id }}</span>
                                </td>
                                <td>
                                    <span class="text-secondary font-13 font-weight-bold">{{ $leave->leave_time }}</span>
                                </td>
                                <td>
                                    @php $sched = $leave->employee->schedules->first(); @endphp
                                    @if($sched)
                                        <span class="text-muted font-12"><i class="ti-time mr-1"></i> {{ $sched->time_in }} - {{ $sched->time_out }}</span>
                                    @else
                                        <span class="text-muted font-11">Not Configured</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($leave->status == 1)
                                        <span class="status-badge badge-active">On Time</span>
                                    @else
                                        <span class="status-badge badge-inactive">Early GO</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No approved leave logs found for today.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
