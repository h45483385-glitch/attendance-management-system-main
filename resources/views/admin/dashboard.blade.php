@extends('layouts.master')

@section('content')
<style>
    /* Notice Board Custom CSS */
    .notice-board { background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-radius: 20px; border: 1px solid #e2e8f0; height: 100%; min-height: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    .notice-item { border-left: 4px solid #3b82f6; background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .notice-item:hover { transform: translateX(5px); }
    .notice-item.urgent { border-left-color: #ef4444; }
    .notice-item.meeting { border-left-color: #f59e0b; }
    
    .notice-date { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    .notice-title { font-size: 14px; font-weight: 700; color: #1e293b; margin: 5px 0; }
    .notice-desc { font-size: 13px; color: #475569; margin: 0; }
    
    /* Stat Card Hover */
    .filter-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important; }
</style>

<!-- TOP STAT CARDS -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="all" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <div class="mb-2"><i class="ti-id-badge text-primary" style="font-size: 24px;"></i></div>
                <p class="text-muted text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.5px;">Total Present</p>
                <h2 class="mb-0 text-primary font-weight-bold" id="metric-present">{{ $totalPresent ?? 0 }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="late" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <div class="mb-2"><i class="ti-alert text-danger" style="font-size: 24px;"></i></div>
                <p class="text-muted text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.5px;">Late Arrivals</p>
                <h2 class="mb-0 text-danger font-weight-bold" id="metric-late">{{ $lateArrivals ?? 0 }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="break" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <div class="mb-2"><i class="ti-alarm-clock text-warning" style="font-size: 24px;"></i></div>
                <p class="text-muted text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.5px;">On Break</p>
                <h2 class="mb-0 text-warning font-weight-bold" id="metric-break">{{ $onBreak ?? 0 }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="ontime" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <div class="mb-2"><i class="ti-check-box text-success" style="font-size: 24px;"></i></div>
                <p class="text-muted text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.5px;">Avg On-Time</p>
                <h2 class="mb-0 text-success font-weight-bold" id="metric-avg-time">{{ $onTimePercentage ?? 100 }}%</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- LEFT SIDE: LIVE ATTENDANCE TABLE -->
    <div class="col-xl-8 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 20px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="mb-0 text-dark font-weight-bold" id="table-title">Live Daily Activity: All Present</h5>
            </div>
            <div class="card-body" id="layer-two-content">
                
                @if(!isset($todayLogs) || $todayLogs->isEmpty())
                    <div class="text-center py-5">
                        <i class="ti-calendar text-muted mb-3" style="font-size: 40px;"></i>
                        <p class="text-muted">No attendance records found for today.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th>Emp ID</th>
                                    <th>Check In</th>
                                    <th>Check Out</th> 
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayLogs as $log)
                                    @php
                                        $isLate = $log->status == '0';
                                        $isOnBreak = \App\Models\BreakLog::where('attendance_id', $log->id)->whereNull('break_end')->exists();
                                        
                                        $category = 'ontime'; 
                                        if ($isOnBreak) $category = 'break';
                                        elseif ($isLate) $category = 'late';
                                    @endphp
                                    
                                    <tr class="activity-row" data-category="{{ $category }}">
                                        <td class="font-weight-bold text-primary">#{{ $log->emp_id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->attendance_time)->format('h:i A') }}</td>
                                        
                                        <td>
                                            @if($log->check_out_time)
                                                {{ \Carbon\Carbon::parse($log->check_out_time)->format('h:i A') }}
                                            @else
                                                <span class="text-muted" style="font-style: italic; font-size: 12px;">Still Working</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            @if($category == 'break')
                                                <span class="badge badge-warning px-2 py-1">On Break</span>
                                            @elseif($category == 'late')
                                                <span class="badge badge-danger px-2 py-1">Late</span>
                                            @else
                                                <span class="badge badge-success px-2 py-1">On Time</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: NOTICE BOARD -->
    <div class="col-xl-4 mb-4">
        <div class="notice-board p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-dark font-weight-bold"><i class="ti-announcement text-primary mr-2"></i> Notice Board</h5>
                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="newAnnouncement()"><i class="ti-plus mr-1"></i> New</button>
            </div>

            <!-- Empty State for Notice Board (Centered Properly) -->
            <div class="text-center my-auto" id="empty-notice">
                <i class="ti-bell text-muted mb-3" style="font-size: 40px; opacity: 0.5;"></i>
                <h6 class="text-muted font-weight-bold">No Announcements Yet</h6>
                <p class="text-muted" style="font-size: 13px;">Click 'New' to publish a circular or meeting alert to all employees.</p>
            </div>

            <!-- Dynamic notices will be appended here later via Database -->
            <div id="notice-list"></div>
        </div>
    </div>
</div>

<!-- SweetAlert2 JS for the "New Announcement" Popup -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Tab Filter Logic
    document.addEventListener("DOMContentLoaded", function() {
        const cards = document.querySelectorAll('.filter-card');
        const rows = document.querySelectorAll('.activity-row');
        const tableTitle = document.getElementById('table-title');

        cards.forEach(card => {
            card.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                if(filter === 'all') tableTitle.innerText = "Live Daily Activity: All Present";
                if(filter === 'late') tableTitle.innerText = "Live Daily Activity: Late Arrivals";
                if(filter === 'break') tableTitle.innerText = "Live Daily Activity: Currently on Break";
                if(filter === 'ontime') tableTitle.innerText = "Live Daily Activity: On-Time Staff";

                rows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-category') === filter) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                });
            });
        });
    });

    // New Announcement Popup Logic
    function newAnnouncement() {
        Swal.fire({
            title: 'Create Announcement',
            html:
                '<input id="swal-title" class="swal2-input" placeholder="Title (e.g. Townhall Meeting)">' +
                '<select id="swal-type" class="swal2-select" style="width: 80%; max-width: 100%;"><option value="general">General Info</option><option value="meeting">Meeting Schedule</option><option value="urgent">Urgent / Alert</option></select>' +
                '<textarea id="swal-desc" class="swal2-textarea" placeholder="Type your message here..."></textarea>',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Publish Now',
            confirmButtonColor: '#4f46e5',
            preConfirm: () => {
                return [
                    document.getElementById('swal-title').value,
                    document.getElementById('swal-type').value,
                    document.getElementById('swal-desc').value
                ]
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Published!', 'Your announcement has been sent to all employees.', 'success');
            }
        });
    }
</script>
@endsection