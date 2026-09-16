<style>
    /* Fix for sidebar sub-menu text overflow in collapsed state */
    #sidebar-menu .submenu {
        width: max-content !important;
    }
    #sidebar-menu .submenu li a {
        white-space: nowrap !important;
    }
    /* Visual highlight for the active child link in the flyout menu */
    #sidebar-menu .submenu li a.active {
        color: #fff !important; /* bright white text */
        background-color: rgba(255, 255, 255, 0.1) !important; /* subtle bg */
        font-weight: 600 !important;
    }
    /* Optional hover effect for active link to reinforce state */
    #sidebar-menu .submenu li a.active:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }
    /* Hide live badge in collapsed/enlarged sidebar to prevent tooltip overlap */
    body.enlarged #sidebar-menu .sidebar-badge-live,
    .enlarged #sidebar-menu .sidebar-badge-live {
        display: none !important;
    }
</style>

<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                
                @php
                    $user = auth()->user();
                    $isAdmin = $user && ($user->hasRole('admin') || $user->hasRole('hr_admin') || $user->role === 'admin' || $user->role === 'hr_admin');
                    $isIT = $user && ($user->hasRole('it-support') || $user->hasRole('developer-it') || $user->role === 'it-support' || $user->role === 'developer-it');
                    $isReceptionist = $user && ($user->hasRole('receptionist') || $user->role === 'receptionist');
                    $isSecurity = $user && ($user->hasRole('security') || $user->role === 'security');
                    $isEmployee = $user && ($user->hasRole('employee') || $user->role === 'employee' || $user->role === 'staff');
                @endphp

                {{-- =========================================================================
                     1. ADMIN / HR ADMIN (SEES EVERYTHING)
                     ========================================================================= --}}
                @if($isAdmin)
                    <li class="menu-title">Main</li>
                    <li>
                        <a href="{{ route('admin') }}" class="waves-effect {{ request()->is('admin') ? 'mm active' : '' }}" title="Dashboard">
                            <i class="ti-home"></i> <span> Dashboard </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('occupancy.index') }}" class="waves-effect {{ request()->is('occupancy*') ? 'mm active' : '' }}" title="Office Occupancy">
                            <i class="ti-direction-alt text-success"></i> <span> Office Occupancy </span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('employees.index') }}" class="waves-effect {{ request()->is('employees*') ? 'mm active' : '' }}" title="Staff Management">
                            <i class="ti-id-badge"></i><span> Staff Management </span>
                        </a>
                    </li>

                    <li class="menu-title">Time & Attendance</li>
                    <li>
                        <a href="{{ route('schedule.index') }}" class="waves-effect {{ request()->is('schedule*') ? 'mm active' : '' }}" title="Schedule">
                            <i class="ti-time"></i> <span> Schedule </span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('check', 'sheet-report', 'overtime*', 'kiosk') ? 'active' : '' }}" title="Attendance">
                            <i class="ti-calendar"></i><span> Attendance </span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-header">Attendance</li>
                            <li><a href="{{ route('check') }}" class="{{ request()->is('check') ? 'active' : '' }}">Today's Live Sheet</a></li>
                            <li><a href="{{ route('sheet-report') }}" class="{{ request()->is('sheet-report') ? 'active' : '' }}">Master Attendance</a></li>
                            <li><a href="{{ route('overtime') }}" class="{{ request()->is('overtime*') ? 'active' : '' }}">Overtime Approvals</a></li>
                            <li><a href="{{ route('kiosk.view') }}" target="_blank" class="{{ request()->is('kiosk') ? 'active' : '' }}"><i class="ti-fullscreen mr-1 text-primary"></i> Attendance Kiosk</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('latetime*') ? 'active' : '' }}" title="Time Exceptions">
                            <i class="dripicons-warning"></i><span> Time Exceptions </span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-header">Time Exceptions</li>
                            <li><a href="{{ route('latetime') }}" class="{{ request()->is('latetime*') ? 'active' : '' }}">Late Arrivals & Requests</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">Reports & Payroll</li>
                    <li>
                        <a href="{{ route('pay.report') }}" class="waves-effect {{ request()->is('pay-report*') ? 'mm active' : '' }}" title="Pay Report">
                            <i class="ti-wallet"></i> <span> Pay Report </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('salary.master') }}" class="waves-effect {{ request()->is('settings/salary-master*') ? 'mm active' : '' }}" title="Salary Master">
                            <i class="ti-credit-card"></i> <span> Salary Master </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.index') }}" class="waves-effect {{ request()->is('reports*') ? 'mm active' : '' }}" title="Reports Hub">
                            <i class="ti-stats-up"></i> <span> Reports Hub </span>
                        </a>
                    </li>

                    <li class="menu-title">Visitors</li>
                    <li class="dropup-item">
                        <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('visitor*') ? 'active' : '' }}" title="Visitor Management">
                            <i class="dripicons-user-group"></i><span> Visitor Management </span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-header">Visitor Management</li>
                            <li><a href="{{ route('visitor.checkin') }}" class="{{ request()->is('visitor-checkin') ? 'active' : '' }}">Visitor Check-In</a></li>
                            <li><a href="{{ route('admin.visitor_index') }}" class="{{ request()->is('visitor-logs') ? 'active' : '' }}">Visitor Logs</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">Administration</li>
                    <li class="dropup-item">
                        <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('users*', 'roles*', 'finger_device*', 'cameras*', 'audit-logs*', 'security*') ? 'active' : '' }}" title="Admin & Security">
                            <i class="ti-shield"></i><span> Admin & Security </span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-header">Admin & Security</li>
                            <li><a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">Users</a></li>
                            <li><a href="{{ route('roles.index') }}" class="{{ request()->is('roles*') ? 'active' : '' }}">Roles & Permissions</a></li>
                            <li><a href="{{ route('finger_device.index') }}" class="{{ request()->is('finger_device*') ? 'active' : '' }}">Devices & Biometrics</a></li>
                            <li><a href="{{ route('cameras.index') }}" class="{{ request()->is('cameras*') ? 'active' : '' }}">Cameras</a></li>
                            <li><a href="{{ route('audit_logs.index') }}" class="{{ request()->is('audit-logs*') ? 'active' : '' }}">Audit Logs</a></li>
                            <li><a href="{{ route('security.dashboard') }}" class="{{ request()->is('security/dashboard*') ? 'active' : '' }}">Security Dashboard</a></li>
                            <li><a href="{{ route('security.settings') }}" class="{{ request()->is('security/settings*') ? 'active' : '' }}">Security Settings</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">System</li>
                    <li>
                        <a href="{{ route('admin.settings') }}" class="waves-effect {{ request()->is('settings') ? 'mm active' : '' }}" title="System Settings">
                            <i class="ti-settings"></i> <span> System Settings </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('holidays.index') }}" class="waves-effect {{ request()->is('settings/holidays*') ? 'mm active' : '' }}" title="Holidays Calendar">
                            <i class="ti-calendar"></i> <span> Holidays Calendar </span>
                        </a>
                    </li>

                {{-- =========================================================================
                     2. IT SUPPORT (SEES ONLY: IT Dashboard, Devices & Biometrics, Cameras, Audit Logs, Security Dashboard)
                     ========================================================================= --}}
                @elseif($isIT)
                    <li class="menu-title">Main</li>
                    <li>
                        <a href="{{ route('it-support.dashboard') }}" class="waves-effect {{ request()->is('it-support*') ? 'mm active' : '' }}" title="IT Dashboard">
                            <i class="ti-desktop"></i> <span> IT Dashboard </span>
                        </a>
                    </li>

                    <li class="menu-title">Infrastructure</li>
                    <li>
                        <a href="{{ route('finger_device.index') }}" class="waves-effect {{ request()->is('finger_device*') ? 'mm active' : '' }}" title="Devices & Biometrics">
                            <i class="ti-server"></i> <span> Devices & Biometrics </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cameras.index') }}" class="waves-effect {{ request()->is('cameras*') ? 'mm active' : '' }}" title="Cameras">
                            <i class="ti-video-camera"></i> <span> Cameras </span>
                        </a>
                    </li>

                    <li class="menu-title">Security & Audit</li>
                    <li>
                        <a href="{{ route('security.dashboard') }}" class="waves-effect {{ request()->is('security/dashboard*') ? 'mm active' : '' }}" title="Security Dashboard">
                            <i class="ti-shield"></i> <span> Security Dashboard </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('audit_logs.index') }}" class="waves-effect {{ request()->is('audit-logs*') ? 'mm active' : '' }}" title="Audit Logs">
                            <i class="ti-notepad"></i> <span> Audit Logs </span>
                        </a>
                    </li>

                {{-- =========================================================================
                     3. RECEPTIONIST (SEES ONLY: Receptionist Dashboard, Visitor Management, Attendance Kiosk)
                     ========================================================================= --}}
                @elseif($isReceptionist)
                    <li class="menu-title">Main</li>
                    <li>
                        <a href="{{ route('receptionist.dashboard') }}" class="waves-effect {{ request()->is('receptionist*') ? 'mm active' : '' }}" title="Receptionist Dashboard">
                            <i class="ti-bell"></i> <span> Receptionist Dashboard </span>
                        </a>
                    </li>

                    <li class="menu-title">Visitors</li>
                    <li class="dropup-item">
                        <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('visitor*') ? 'active' : '' }}" title="Visitor Management">
                            <i class="dripicons-user-group"></i><span> Visitor Management </span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-header">Visitor Management</li>
                            <li><a href="{{ route('visitor.checkin') }}" class="{{ request()->is('visitor-checkin') ? 'active' : '' }}">Visitor Check-In</a></li>
                            <li><a href="{{ route('admin.visitor_index') }}" class="{{ request()->is('visitor-logs') ? 'active' : '' }}">Visitor Logs</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">Terminal</li>
                    <li>
                        <a href="{{ route('kiosk.view') }}" target="_blank" class="waves-effect {{ request()->is('kiosk') ? 'mm active' : '' }}" title="Attendance Kiosk">
                            <i class="ti-fullscreen text-primary"></i> <span> Attendance Kiosk </span>
                        </a>
                    </li>

                {{-- =========================================================================
                     4. SECURITY (SEES ONLY: Security Dashboard, Visitor Logs, Fallback Check-in)
                     ========================================================================= --}}
                @elseif($isSecurity)
                    <li class="menu-title">Main</li>
                    <li>
                        <a href="{{ route('security.dashboard') }}" class="waves-effect {{ request()->is('security/dashboard*') ? 'mm active' : '' }}" title="Security Dashboard">
                            <i class="ti-shield"></i> <span> Security Dashboard </span>
                        </a>
                    </li>

                    <li class="menu-title">Surveillance & Visitors</li>
                    <li>
                        <a href="{{ route('admin.visitor_index') }}" class="waves-effect {{ request()->is('visitor-logs*') ? 'mm active' : '' }}" title="Visitor Logs">
                            <i class="dripicons-user-group"></i> <span> Visitor Logs </span>
                        </a>
                    </li>

                    <li class="menu-title">Attendance</li>
                    <li>
                        <a href="{{ route('kiosk.view') }}" target="_blank" class="waves-effect {{ request()->is('kiosk') ? 'mm active' : '' }}" title="Fallback Check-in">
                            <i class="ti-key text-warning"></i> <span> Fallback Check-in </span>
                        </a>
                    </li>

                {{-- =========================================================================
                     5. REGULAR EMPLOYEE (SEES ONLY: My Attendance, Leave/Overtime Application, Personal Payslip)
                     ========================================================================= --}}
                @elseif($isEmployee)
                    <li class="menu-title">Main</li>
                    <li>
                        <a href="{{ route('attendance.dashboard') }}" class="waves-effect {{ request()->is('dashboard*') ? 'mm active' : '' }}" title="My Attendance">
                            <i class="ti-home"></i> <span> My Attendance </span>
                        </a>
                    </li>

                    <li class="menu-title">Requests & Time</li>
                    <li>
                        <a href="{{ route('leave') }}" class="waves-effect {{ request()->is('leave*') ? 'mm active' : '' }}" title="Leave Application">
                            <i class="ti-calendar"></i> <span> Leave Application </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('overtime') }}" class="waves-effect {{ request()->is('overtime*') ? 'mm active' : '' }}" title="Overtime Application">
                            <i class="ti-time"></i> <span> Overtime Application </span>
                        </a>
                    </li>

                    <li class="menu-title">Finance & Payroll</li>
                    <li>
                        <a href="{{ route('pay.report') }}" class="waves-effect {{ request()->is('pay-report*') ? 'mm active' : '' }}" title="Personal Payslip">
                            <i class="ti-wallet"></i> <span> Personal Payslip </span>
                        </a>
                    </li>
                @endif

            </ul>        
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Left Sidebar End -->