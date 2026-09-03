@extends('layouts.master')

@section('css')
<style>
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
    
    .badge-action {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .badge-login { background-color: #e0f2fe; color: #0369a1; }
    .badge-user { background-color: #f0fdf4; color: #15803d; }
    .badge-security { background-color: #fee2e2; color: #b91c1c; }
    .badge-default { background-color: #f1f5f9; color: #475569; }

    pre.json-render {
        background: #0f172a;
        color: #38bdf8;
        padding: 15px;
        border-radius: 8px;
        font-size: 12px;
        max-height: 250px;
        overflow-y: auto;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Security Audit Trail</h4>
    <p class="text-secondary font-13 mb-0">Immutable record of logins, security policy modifications, administrative changes, and sync heartbeats.</p>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-12 col-lg-12">

        <!-- SEARCH & FILTER FORM -->
        <div class="card saas-card mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-filter text-primary mr-2"></i> Query Logs</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('audit_logs.index') }}" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="font-weight-bold text-muted font-11 mb-1">Search Keyword</label>
                            <input type="text" name="search" class="form-control form-control-saas" value="{{ request('search') }}" placeholder="Description, Actor, IP...">
                        </div>
                        
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="font-weight-bold text-muted font-11 mb-1">Actor</label>
                            <select name="actor" class="form-control form-control-saas">
                                <option value="">All Actors</option>
                                @foreach($actors as $act)
                                    <option value="{{ $act }}" {{ request('actor') == $act ? 'selected' : '' }}>{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="font-weight-bold text-muted font-11 mb-1">Module</label>
                            <select name="module" class="form-control form-control-saas">
                                <option value="">All Modules</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="font-weight-bold text-muted font-11 mb-1">Date Range</label>
                            <div class="input-group">
                                <input type="date" name="start_date" class="form-control form-control-saas" value="{{ request('start_date') }}">
                                <input type="date" name="end_date" class="form-control form-control-saas" value="{{ request('end_date') }}">
                            </div>
                        </div>

                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-saas btn-saas-primary w-100" style="padding: 10px;"><i class="ti-reload mr-1"></i> Filter Logs</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- AUDIT LOGS DISPLAY CARD -->
        <div class="card saas-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-receipt text-primary mr-2"></i> Audit Records</h6>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th class="text-right" style="width: 80px;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="text-dark font-weight-bold font-12">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td>
                                    <span class="text-primary font-weight-bold">{{ $log->actor_name }}</span>
                                    <span class="text-muted font-10 d-block">ID: #{{ $log->actor_id ?? 'System' }}</span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'badge-default';
                                        if(str_contains($log->action, 'LOGIN')) $badgeClass = 'badge-login';
                                        elseif(str_contains($log->action, 'USER') || str_contains($log->action, 'ROLE')) $badgeClass = 'badge-user';
                                        elseif(str_contains($log->action, 'PERMISSION') || str_contains($log->action, 'SECURITY')) $badgeClass = 'badge-security';
                                    @endphp
                                    <span class="badge-action {{ $badgeClass }}">{{ $log->action }}</span>
                                </td>
                                <td>{{ $log->module }}</td>
                                <td>
                                    <span class="text-secondary font-13" title="{{ $log->description }}">{{ Str::limit($log->description, 60) }}</span>
                                </td>
                                <td>
                                    <span class="font-12 font-weight-bold">{{ $log->ip_address }}</span>
                                </td>
                                <td class="text-right">
                                    @if($log->before_data || $log->after_data)
                                        <button class="action-icon-btn" data-toggle="modal" data-target="#detailsModal{{ $log->id }}" title="View JSON Diff"><i class="ti-search"></i></button>
                                    @else
                                        <span class="text-muted font-11">None</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No security events match the current filter query.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="card-footer bg-white border-top p-4">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- ==============================================
     JSON DETAIL MODALS
=============================================== -->
@foreach($logs as $log)
@if($log->before_data || $log->after_data)
<div class="modal fade" id="detailsModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-code text-primary mr-2"></i> Event Data Logs (ID: #{{ $log->id }})</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    @if($log->before_data)
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="font-weight-bold text-muted font-12 mb-2 uppercase text-danger"><i class="ti-arrow-left"></i> Before Payload</h6>
                        <pre class="json-render">{{ json_encode(json_decode($log->before_data), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif

                    @if($log->after_data)
                    <div class="col-md-{{ $log->before_data ? '6' : '12' }}">
                        <h6 class="font-weight-bold text-muted font-12 mb-2 uppercase text-success"><i class="ti-arrow-right"></i> After Payload</h6>
                        <pre class="json-render">{{ json_encode(json_decode($log->after_data), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3 px-4">
                <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection
