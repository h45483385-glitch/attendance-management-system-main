@extends('layouts.master')

@section('css')
<style>
    /* Compact statistics and charts styling */
    .metric-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
    }
    
    .activity-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 12px;
    }
    
    .employee-row-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--primary-blue);
        font-size: 12px;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Dashboard</h4>
    <p class="text-secondary font-13 mb-0">Welcome back, Admin! Here's what's happening today.</p>
</div>
@endsection

@section('content')


<!-- 1. TOP METRICS CARDS ROW -->
<div class="row mt-3">
    <!-- Metric 1: Total Employees -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Total Employees</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($totalEmployees) }}</h3>
                    <span class="badge badge-success mt-2"><i class="ti-arrow-up mr-1"></i> Active Roster</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                    <i class="ti-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 2: Present Today -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Present Today</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($presentToday) }}</h3>
                    <span class="badge badge-success mt-2"><i class="ti-arrow-up mr-1"></i> Checked In</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(34, 197, 94, 0.08); color: var(--primary-green);">
                    <i class="ti-check-box"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 3: Absent Today -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Absent Today</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($absentToday) }}</h3>
                    <span class="badge badge-danger mt-2"><i class="ti-arrow-down mr-1"></i> Not Synced</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(239, 68, 68, 0.08); color: #ef4444;">
                    <i class="ti-close"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 4: Devices Online -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Devices Online</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($devicesOnline) }}</h3>
                    <span class="badge badge-success mt-2"><i class="ti-signal mr-1"></i> Active Hub</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                    <i class="ti-server"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. ANALYTICS ROW -->
<div class="row">
    <!-- Attendance Overview Line Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-stats-up text-primary mr-2"></i> Attendance Overview (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Status Donut Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-pie-chart text-primary mr-2"></i> Biometric Setup Status</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted font-13"><span class="activity-indicator" style="background-color: var(--primary-blue);"></span> Enrolled Staff</span>
                        <span class="font-weight-bold text-dark">{{ $statusActive }} ({{ $totalEmployees > 0 ? round(($statusActive / $totalEmployees)*100) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted font-13"><span class="activity-indicator" style="background-color: #cbd5e1;"></span> Pending Setup</span>
                        <span class="font-weight-bold text-dark">{{ $statusPending }} ({{ $totalEmployees > 0 ? round(($statusPending / $totalEmployees)*100) : 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. LISTS ROW -->
<div class="row">
    <!-- Recent Employees List -->
    <div class="col-lg-6 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Recent Additions</h6>
                <a href="/employees" class="btn btn-sm btn-saas btn-saas-secondary py-1 px-3">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="border: none;">
                        <tbody>
                            @forelse($recentEmployees as $emp)
                            <tr>
                                <td style="border: none; padding: 12px 20px !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="employee-row-img mr-3">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark font-14">{{ $emp->name }}</h6>
                                            <span class="text-muted font-11">#{{ $emp->id }} | {{ $emp->position }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right text-muted font-12" style="border: none; padding: 12px 20px !important;">
                                    {{ \Carbon\Carbon::parse($emp->created_at)->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center py-4 text-muted" style="border: none;">No employees registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Log -->
    <div class="col-lg-6 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-receipt text-primary mr-2"></i> Recent Security Activities</h6>
                <a href="/audit-logs" class="btn btn-sm btn-saas btn-saas-secondary py-1 px-3">View Logs</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="border: none;">
                        <tbody>
                            @forelse($recentActivities as $act)
                            <tr class="activity-row" data-category="{{ str_contains($act->action, 'LATE') ? 'late' : (str_contains($act->action, 'BREAK') ? 'break' : 'ontime') }}" style="border: none;">
                                <td style="border: none; padding: 12px 20px !important;">
                                    <span class="activity-indicator" style="background-color: {{ str_contains($act->action, 'LOGIN_FAILED') || str_contains($act->action, 'LOCKOUT') ? '#ef4444' : (str_contains($act->action, 'REGISTERED') ? 'var(--primary-green)' : 'var(--primary-blue)') }};"></span>
                                    <span class="text-dark font-weight-bold font-13 mr-1">{{ $act->actor_name }}</span>
                                    <span class="text-secondary font-12">{{ $act->description }}</span>
                                </td>
                                <td class="text-right text-muted font-12" style="border: none; padding: 12px 20px !important;">
                                    {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center py-4 text-muted" style="border: none;">No system logs available.</td>
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

@section('script')
<!-- ChartJS CDN for premium data visualization charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Line Chart: Attendance Overview
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDays) !!},
            datasets: [
                {
                    label: 'Present Today',
                    data: {!! json_encode($chartPresent) !!},
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.04)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Absent Today',
                    data: {!! json_encode($chartAbsent) !!},
                    borderColor: '#116fb7',
                    backgroundColor: 'rgba(17, 111, 183, 0.04)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { family: 'Poppins', size: 12 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 2. Donut Chart: Biometric Setup
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Enrolled', 'Pending'],
            datasets: [{
                data: [{{ $statusActive }}, {{ $statusPending }}],
                backgroundColor: ['#116fb7', '#cbd5e1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endsection