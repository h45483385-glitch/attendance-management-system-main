@extends('layouts.master')

@php
    $currentRange = request('range', 'all');
    $rangeLabels = [
        'all' => 'All Records',
        '1h'  => 'Last 1 Hour',
        '24h' => 'Today',
        '7d'  => 'Last 7 Days',
        '30d' => 'Last 30 Days'
    ];
    $activeLabel = $rangeLabels[$currentRange] ?? 'All Records';
@endphp

@section('content')
<style>
    /* Modern Indigo Theme Table */
    .table-modern td, .table-modern th { vertical-align: middle; border-top: 1px solid #f1f5f9; padding: 15px; }
    .table-modern thead th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; white-space: nowrap; }
    .table-modern tbody td { font-size: 13px; color: #334155; font-weight: 500; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }
    
    .card-modern { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; }
    
    /* Stats Cards */
    .stat-card { border-radius: 12px; border: none; overflow: hidden; position: relative; }
    .stat-card .card-body { padding: 25px; z-index: 2; position: relative; }
    .stat-card-icon { position: absolute; right: -10px; bottom: -20px; font-size: 100px; opacity: 0.05; z-index: 1; transform: rotate(-15deg); }
    
    /* Buttons */
    .btn-checkout { background: #fee2e2; color: #dc2626; border: none; font-weight: 600; border-radius: 6px; padding: 6px 12px; font-size: 11px; transition: all 0.2s; }
    .btn-checkout:hover { background: #fca5a5; color: #991b1b; }
    
    .status-inside { background: #dcfce7; color: #166534; font-weight: 600; padding: 5px 12px; border-radius: 20px; font-size: 11px; display: inline-flex; align-items: center; }
    .status-completed { background: #f1f5f9; color: #64748b; font-weight: 600; padding: 5px 12px; border-radius: 20px; font-size: 11px; display: inline-flex; align-items: center; }
    
    .btn-indigo-outline { border: 2px solid #4f46e5; color: #4f46e5; background: white; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
    .btn-indigo-outline:hover { background: #4f46e5; color: white; }
</style>

<div class="row mb-4 pt-3">
    <!-- Total Visitors -->
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card stat-card bg-white shadow-sm" style="border-bottom: 4px solid #4f46e5;">
            <div class="card-body">
                <h6 class="text-uppercase text-muted font-weight-bold mb-1 font-12">Total Visitors</h6>
                <h2 class="mb-0" style="color: #312e81; font-weight: 800;">{{ $totalVisitors ?? 0 }}</h2>
                <i class="mdi mdi-account-group stat-card-icon" style="color: #4f46e5;"></i>
            </div>
        </div>
    </div>

    <!-- Currently Inside -->
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card stat-card bg-white shadow-sm" style="border-bottom: 4px solid #10b981;">
            <div class="card-body">
                <h6 class="text-uppercase text-muted font-weight-bold mb-1 font-12">Currently Inside</h6>
                <h2 class="mb-0" style="color: #064e3b; font-weight: 800;">{{ $currentlyInside ?? 0 }}</h2>
                <i class="mdi mdi-door-open stat-card-icon" style="color: #10b981;"></i>
            </div>
        </div>
    </div>

    <!-- Total Exits -->
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card stat-card bg-white shadow-sm" style="border-bottom: 4px solid #64748b;">
            <div class="card-body">
                <h6 class="text-uppercase text-muted font-weight-bold mb-1 font-12">Total Check-Outs</h6>
                <h2 class="mb-0" style="color: #334155; font-weight: 800;">{{ $totalExits ?? 0 }}</h2>
                <i class="mdi mdi-account-check stat-card-icon" style="color: #64748b;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('visitor.checkin') }}" class="btn btn-indigo-outline px-4 py-2 shadow-sm">
                <i class="mdi mdi-plus-circle mr-1"></i> New Visitor Check-In
            </a>
        </div>
        <div>
            <a href="{{ route('visitor.export') }}" class="btn btn-light shadow-sm px-4 py-2 mr-2 font-weight-bold text-dark" style="border-radius: 8px;">
                <i class="mdi mdi-file-excel text-success mr-1"></i> Export Log
            </a>
            <div class="dropdown d-inline-block">
                <button class="btn btn-light shadow-sm dropdown-toggle px-4 py-2 font-weight-bold text-dark" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px;">
                    <i class="mdi mdi-filter-variant mr-1 text-primary"></i> {{ $activeLabel }}
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border-0 mt-2" aria-labelledby="filterDropdown" style="border-radius: 8px; min-width: 200px;">
                    <a class="dropdown-item py-2 {{ $currentRange == 'all' ? 'bg-light text-primary font-weight-bold' : '' }}" href="?range=all">All Records</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '1h' ? 'bg-light text-primary font-weight-bold' : '' }}" href="?range=1h">Last 1 Hour</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '24h' ? 'bg-light text-primary font-weight-bold' : '' }}" href="?range=24h">Today</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '7d' ? 'bg-light text-primary font-weight-bold' : '' }}" href="?range=7d">Last 7 Days</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '30d' ? 'bg-light text-primary font-weight-bold' : '' }}" href="?range=30d">Last 30 Days</a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px;">
    <i class="mdi mdi-check-circle mr-2"></i><strong>Success!</strong> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card card-modern">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark font-weight-bold"><i class="mdi mdi-format-list-bulleted text-primary mr-2"></i> Visitor Log Book</h5>
                <!-- Simple Search Box -->
                <div class="input-group" style="width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-magnify text-muted"></i></span>
                    </div>
                    <input type="text" id="searchInput" class="form-control bg-light border-left-0" placeholder="Search visitors...">
                </div>
            </div>
            
            <div class="card-body p-0"> 
                <div class="table-responsive"> 
                    <table class="table table-modern table-hover mb-0" id="visitorTable">
                        <thead>
                            <tr>
                                <th>Visitor Info</th>
                                <th>Contact & Company</th>
                                <th>Purpose & Meeting</th>
                                <th>Check-In</th>
                                <th>Check-Out</th> 
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visitors as $visitor)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $visitor->name }}</div>
                                </td>
                                <td>
                                    <div><i class="mdi mdi-phone text-muted mr-1"></i> {{ $visitor->phone }}</div>
                                    @if($visitor->company)
                                        <div class="text-muted font-11 mt-1"><i class="mdi mdi-domain mr-1"></i> {{ $visitor->company }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $visitor->person_to_meet }}</div>
                                    <div class="text-muted font-11 mt-1">{{ $visitor->purpose }}</div>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($visitor->check_in_time)->format('M d, Y') }}</div>
                                    <div class="text-muted font-11 mt-1"><i class="mdi mdi-clock-outline mr-1"></i> {{ \Carbon\Carbon::parse($visitor->check_in_time)->format('h:i A') }}</div>
                                </td>
                                <td>
                                    @if($visitor->check_out_time)
                                        <div>{{ \Carbon\Carbon::parse($visitor->check_out_time)->format('M d, Y') }}</div>
                                        <div class="text-muted font-11 mt-1"><i class="mdi mdi-clock-outline mr-1"></i> {{ \Carbon\Carbon::parse($visitor->check_out_time)->format('h:i A') }}</div>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @if($visitor->status == 'Inside')
                                        <span class="status-inside"><span class="mdi mdi-circle mr-1" style="font-size: 8px;"></span> Active</span>
                                    @else
                                        <span class="status-completed"><i class="mdi mdi-check-all mr-1"></i> Completed</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($visitor->status == 'Inside')
                                        <form action="{{ route('visitor.checkout', $visitor->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-checkout shadow-sm">
                                                <i class="mdi mdi-logout mr-1"></i> Check-Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Done</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="my-3">
                                        <i class="mdi mdi-account-search-outline display-4 text-muted" style="opacity: 0.3;"></i>
                                        <h5 class="mt-3 text-dark font-weight-bold">No Visitors Found</h5>
                                        <p class="text-muted small">There are no records for the selected time range.</p>
                                    </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#visitorTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endsection