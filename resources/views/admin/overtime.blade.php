@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Global Overtime Approvals Management</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin">Home</a></li>
            <li class="breadcrumb-item active">Over Time Approvals</li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="/leave" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-table mr-2"></i>Leave Table</a>
@endsection

@section('content')
@include('includes.flash')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                            <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Over Time</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action (Approve / Reject)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($overtimes) && $overtimes->count() > 0)
                                        @foreach ($overtimes as $overtime)
                                            <tr>
                                                <td>{{ $overtime->overtime_date }}</td>
                                                <td>{{ $overtime->emp_id }}</td>
                                                <td>{{ $overtime->employee->name ?? 'N/A' }}</td>
                                                <td><span class="font-weight-bold text-success">{{ $overtime->duration }}</span></td>
                                                <td>{{ $overtime->employee->schedules->first()->time_in ?? 'N/A' }}</td>
                                                <td>{{ $overtime->employee->schedules->first()->time_out ?? 'N/A' }}</td>
                                                
                                                <td class="text-center align-middle">
                                                    @php $status = $overtime->status ?? 'Pending'; @endphp
                                                    @if($status == 'Approved')
                                                        <span class="badge badge-success px-3 py-1">Approved</span>
                                                    @elseif($status == 'Rejected')
                                                        <span class="badge badge-danger px-3 py-1">Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning px-3 py-1 text-dark">Pending</span>
                                                    @endif
                                                </td>

                                                <td class="text-center align-middle">
                                                    @if(($overtime->status ?? 'Pending') == 'Pending')
                                                        <form action="/overtime/approve/{{ $overtime->id }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success px-2 py-1 font-weight-bold mr-1" title="Approve">
                                                                <i class="mdi mdi-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form action="/overtime/reject/{{ $overtime->id }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger px-2 py-1 font-weight-bold" title="Reject">
                                                                <i class="mdi mdi-close"></i> Reject
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted font-italic" style="font-size: 12px;">Action Completed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                No pending or global overtime records found.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection