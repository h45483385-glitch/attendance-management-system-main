@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Overtime Wallet Log: {{ $employee->name ?? 'Employee' }} (ID: #{{ $employee->id }})</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin">Home</a></li>
            <li class="breadcrumb-item"><a href="/sheet-report">Master Attendance</a></li>
            <li class="breadcrumb-item active">Overtime Wallet</li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="/sheet-report" class="btn btn-secondary btn-sm btn-flat"><i class="mdi mdi-arrow-left mr-2"></i>Back to Master Sheet</a>
@endsection

@section('content')
@include('includes.flash')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                            <table class="table table-striped table-bordered dt-responsive nowrap" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Over Time Duration</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th class="text-center">Approval Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($overtimes) && $overtimes->count() > 0)
                                        @foreach ($overtimes as $ot)
                                            <tr>
                                                <td>{{ $ot->overtime_date }}</td>
                                                <td><span class="font-weight-bold text-success">{{ $ot->duration }} Hrs</span></td>
                                                <td>{{ $employee->schedules->first()->time_in ?? 'N/A' }}</td>
                                                <td>{{ $employee->schedules->first()->time_out ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    @if($ot->status == 'Approved')
                                                        <span class="badge badge-success px-3 py-1">Approved & Added to Pay</span>
                                                    @elseif($ot->status == 'Rejected')
                                                        <span class="badge badge-danger px-3 py-1">Rejected by Admin</span>
                                                    @else
                                                        <span class="badge badge-warning px-3 py-1 text-dark">Pending Approval</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No overtime records found for this employee.
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