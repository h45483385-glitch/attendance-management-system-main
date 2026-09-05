@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Annual Holiday Calendar</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin') }}" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.settings') }}" class="text-primary font-weight-bold">Settings</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Holiday Calendar</li>
    </ol>
</div>
<div class="col-sm-6 text-right">
    <button class="btn btn-primary px-4 py-2 font-weight-bold rounded-pill shadow-sm" data-toggle="modal" data-target="#addHolidayModal" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
        <i class="fas fa-plus-circle mr-1"></i> Add Holiday
    </button>
</div>
@endsection

@section('content')
<style>
    /* Premium Calendar Styling */
    .calendar-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }
    .calendar-header-title {
        font-weight: 700;
        font-size: 1.25rem;
        color: #1e293b;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 700;
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        padding: 10px 0;
        letter-spacing: 0.5px;
    }
    .calendar-cell {
        min-height: 100px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 8px;
        transition: all 0.2s ease;
        position: relative;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .calendar-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .calendar-cell.other-month {
        background: #f8fafc;
        opacity: 0.5;
        cursor: not-allowed;
    }
    .calendar-cell.is-today {
        border: 2px solid #3b82f6 !important;
        background: #eff6ff !important;
    }
    .calendar-cell.is-weekend {
        background: #fafafa;
    }
    .calendar-cell.holiday-public {
        background: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
    }
    .calendar-cell.holiday-company {
        background: #eff6ff !important;
        border-left: 4px solid #3b82f6 !important;
    }
    .calendar-cell.holiday-optional {
        background: #fffbeb !important;
        border-left: 4px solid #f59e0b !important;
    }
    .cell-date-number {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
    }
    .cell-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 6px;
        border-radius: 6px;
        line-height: 1.2;
        margin-top: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    .badge-public { background: #fee2e2; color: #991b1b; }
    .badge-company { background: #dbeafe; color: #1e40af; }
    .badge-optional { background: #fef3c7; color: #92400e; }
    .badge-weekend { background: #f1f5f9; color: #64748b; font-size: 10px; }

    /* Legend Pills */
    .legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        margin-right: 8px;
    }
    .dot-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show font-weight-bold" role="alert">
    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show font-weight-bold" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i> {{ $errors->first() }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
@endif

<!-- Metric Cards Row -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="calendar-card p-3 d-flex align-items-center">
            <div class="mr-3 p-3 rounded-circle text-white bg-primary">
                <i class="fas fa-calendar-alt fa-lg"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold text-uppercase">Total Holidays ({{ $year }})</div>
                <h4 class="font-weight-bold mb-0 text-dark">{{ $holidays->count() }} Days</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="calendar-card p-3 d-flex align-items-center">
            <div class="mr-3 p-3 rounded-circle text-white bg-danger">
                <i class="fas fa-landmark fa-lg"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold text-uppercase">Public Holidays</div>
                <h4 class="font-weight-bold mb-0 text-danger">{{ $publicCount }} Days</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="calendar-card p-3 d-flex align-items-center">
            <div class="mr-3 p-3 rounded-circle text-white" style="background-color: #3b82f6;">
                <i class="fas fa-building fa-lg"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold text-uppercase">Company Leaves</div>
                <h4 class="font-weight-bold mb-0 text-primary">{{ $companyCount }} Days</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="calendar-card p-3 d-flex align-items-center">
            <div class="mr-3 p-3 rounded-circle text-white bg-warning">
                <i class="fas fa-star fa-lg"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold text-uppercase">Optional / Floating</div>
                <h4 class="font-weight-bold mb-0 text-warning">{{ $optionalCount }} Days</h4>
            </div>
        </div>
    </div>
</div>

<!-- Main Calendar Card -->
<div class="calendar-card p-4 mb-4">
    @php
        $currentMonthCarbon = \Carbon\Carbon::createFromDate($year, $month, 1);
        $prevMonth = $currentMonthCarbon->copy()->subMonth();
        $nextMonth = $currentMonthCarbon->copy()->addMonth();

        $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();

        // 0 = Sunday, 1 = Monday ... 6 = Saturday
        $startDayOfWeek = $startOfMonth->dayOfWeek; 
        $daysInMonth = $currentMonthCarbon->daysInMonth;

        $isSatOff = $setting ? (bool)$setting->is_saturday_off : false;
        $isSunOff = $setting ? (bool)$setting->is_sunday_off : true;

        $holidaysByDate = $holidays->keyBy(function($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
        });
    @endphp

    <!-- Calendar Controls & Legend -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center mb-2 mb-md-0">
            <a href="{{ route('holidays.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="btn btn-light btn-sm mr-2 rounded-circle p-2">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="mb-0 font-weight-bold text-dark mr-3" style="font-size: 1.3rem;">
                {{ $currentMonthCarbon->format('F Y') }}
            </h4>
            <a href="{{ route('holidays.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="btn btn-light btn-sm mr-3 rounded-circle p-2">
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('holidays.index', ['year' => \Carbon\Carbon::now()->year, 'month' => \Carbon\Carbon::now()->month]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 font-weight-bold">
                Today
            </a>
        </div>

        <!-- Legend Pills -->
        <div class="d-flex flex-wrap align-items-center">
            <span class="legend-pill" style="background:#fee2e2; color:#991b1b;"><span class="dot-indicator" style="background:#ef4444;"></span> Public Holiday</span>
            <span class="legend-pill" style="background:#dbeafe; color:#1e40af;"><span class="dot-indicator" style="background:#3b82f6;"></span> Company Off</span>
            <span class="legend-pill" style="background:#fef3c7; color:#92400e;"><span class="dot-indicator" style="background:#f59e0b;"></span> Optional</span>
            <span class="legend-pill" style="background:#f1f5f9; color:#64748b;"><span class="dot-indicator" style="background:#94a3b8;"></span> Weekend</span>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-grid mb-2">
        <div class="calendar-day-header text-danger">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header text-danger">Sat</div>
    </div>

    <div class="calendar-grid">
        {{-- Leading empty days from prev month --}}
        @for($i = 0; $i < $startDayOfWeek; $i++)
            <div class="calendar-cell other-month"></div>
        @endfor

        {{-- Month Days --}}
        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $dateString = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $cellCarbon = \Carbon\Carbon::createFromDate($year, $month, $d);
                $isToday = $cellCarbon->isToday();
                $isWeekend = ($isSunOff && $cellCarbon->dayOfWeek === \Carbon\Carbon::SUNDAY)
                          || ($isSatOff && $cellCarbon->dayOfWeek === \Carbon\Carbon::SATURDAY);

                $holiday = $holidaysByDate->get($dateString);
                $holidayClass = '';
                if ($holiday) {
                    $holidayClass = 'holiday-' . $holiday->type;
                } elseif ($isWeekend) {
                    $holidayClass = 'is-weekend';
                }
            @endphp

            <div class="calendar-cell {{ $holidayClass }} {{ $isToday ? 'is-today' : '' }}" 
                 onclick="handleDateClick('{{ $dateString }}', '{{ $holiday ? $holiday->id : '' }}', '{{ $holiday ? addslashes($holiday->title) : '' }}', '{{ $holiday ? $holiday->type : 'public' }}')">
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="cell-date-number {{ ($cellCarbon->dayOfWeek === \Carbon\Carbon::SUNDAY || ($isSatOff && $cellCarbon->dayOfWeek === \Carbon\Carbon::SATURDAY)) ? 'text-danger' : '' }}">
                        {{ $d }}
                    </span>
                    @if($isToday)
                        <span class="badge badge-primary px-2" style="font-size: 10px;">TODAY</span>
                    @endif
                </div>

                <div>
                    @if($holiday)
                        <span class="cell-badge badge-{{ $holiday->type }}" title="{{ $holiday->title }}">
                            <i class="fas fa-tag mr-1"></i> {{ $holiday->title }}
                        </span>
                    @elseif($isWeekend)
                        <span class="cell-badge badge-weekend">
                            <i class="fas fa-bed mr-1"></i> Weekly Off
                        </span>
                    @endif
                </div>
            </div>
        @endfor

        {{-- Trailing days to complete 7 columns grid row --}}
        @php
            $totalCells = $startDayOfWeek + $daysInMonth;
            $remaining = (7 - ($totalCells % 7)) % 7;
        @endphp
        @for($j = 0; $j < $remaining; $j++)
            <div class="calendar-cell other-month"></div>
        @endfor
    </div>
</div>

<!-- Holidays List Table -->
<div class="calendar-card p-4">
    <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-list-ul mr-2 text-primary"></i> Scheduled Holidays ({{ $year }})</h5>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0">Date</th>
                    <th class="border-0">Day</th>
                    <th class="border-0">Holiday Title</th>
                    <th class="border-0">Classification</th>
                    <th class="border-0 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $h)
                    @php $hDate = \Carbon\Carbon::parse($h->date); @endphp
                    <tr>
                        <td class="align-middle font-weight-bold text-dark">
                            {{ $hDate->format('d M, Y') }}
                        </td>
                        <td class="align-middle text-muted">
                            {{ $hDate->format('l') }}
                        </td>
                        <td class="align-middle font-weight-bold" style="color: #334155;">
                            {{ $h->title }}
                        </td>
                        <td class="align-middle">
                            @if($h->type === 'public')
                                <span class="badge badge-danger px-3 py-1 font-weight-bold">Public / Gazetted</span>
                            @elseif($h->type === 'company')
                                <span class="badge badge-primary px-3 py-1 font-weight-bold">Company Holiday</span>
                            @else
                                <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold">Optional / Floating</span>
                            @endif
                        </td>
                        <td class="align-middle text-right">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 mr-1 font-weight-bold" 
                                    onclick="editHolidayModal({{ $h->id }}, '{{ $h->date }}', '{{ addslashes($h->title) }}', '{{ $h->type }}')">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <form action="{{ route('holidays.destroy', $h->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this holiday? Working days and payroll will adjust dynamically.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 font-weight-bold">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times text-muted fa-2x mb-2 d-block"></i>
                            No official holidays registered for year {{ $year }}. Click "Add Holiday" above or select any date on the calendar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- ADD / EDIT HOLIDAY MODAL                    -->
<!-- ========================================== -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="holidayForm" action="{{ route('holidays.store') }}" method="POST" class="modal-content" style="border-radius: 12px; border: none;">
            @csrf
            <div id="methodContainer"></div>
            
            <div class="modal-header text-white" style="background-color: #10b981; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title font-weight-bold" id="holidayModalTitle"><i class="fas fa-calendar-plus mr-2"></i> Register Official Holiday</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3">
                    <label class="form-label font-weight-bold text-muted small text-uppercase">Holiday Date</label>
                    <input type="date" name="date" id="holidayDateInput" class="form-control font-weight-bold" required>
                    <small class="text-muted">Weekends and existing holidays are protected against wage docking.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold text-muted small text-uppercase">Holiday Title</label>
                    <input type="text" name="title" id="holidayTitleInput" class="form-control font-weight-bold" placeholder="e.g. Independence Day / Diwali" required>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold text-muted small text-uppercase">Holiday Classification</label>
                    <select name="type" id="holidayTypeInput" class="form-control form-select font-weight-bold">
                        <option value="public">Government Public / Gazetted Holiday</option>
                        <option value="company">Company Specified Holiday</option>
                        <option value="optional">Optional / Floating Holiday</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0">
                <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="saveHolidayBtn" class="btn btn-success px-4 font-weight-bold rounded-pill">
                    <i class="fas fa-check-circle mr-1"></i> Save Holiday
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleDateClick(dateStr, holidayId, holidayTitle, holidayType) {
        if (holidayId) {
            editHolidayModal(holidayId, dateStr, holidayTitle, holidayType);
        } else {
            document.getElementById('holidayModalTitle').innerHTML = '<i class="fas fa-calendar-plus mr-2"></i> Schedule Holiday on ' + dateStr;
            document.getElementById('holidayForm').action = "{{ route('holidays.store') }}";
            document.getElementById('methodContainer').innerHTML = '';
            document.getElementById('holidayDateInput').value = dateStr;
            document.getElementById('holidayTitleInput').value = '';
            document.getElementById('holidayTypeInput').value = 'public';
            document.getElementById('saveHolidayBtn').innerHTML = '<i class="fas fa-check-circle mr-1"></i> Schedule Holiday';

            $('#addHolidayModal').modal('show');
        }
    }

    function editHolidayModal(id, dateStr, title, type) {
        document.getElementById('holidayModalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Update Holiday Entry';
        document.getElementById('holidayForm').action = '/settings/holidays/' + id;
        document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('holidayDateInput').value = dateStr;
        document.getElementById('holidayTitleInput').value = title;
        document.getElementById('holidayTypeInput').value = type;
        document.getElementById('saveHolidayBtn').innerHTML = '<i class="fas fa-sync mr-1"></i> Update Holiday';

        $('#addHolidayModal').modal('show');
    }
</script>
@endsection
