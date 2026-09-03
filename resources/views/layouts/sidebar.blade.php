<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                
                <li class="menu-title">Main</li>
                <li>
                    <a href="{{route('admin')}}" class="waves-effect {{ request()->is('admin') ? 'mm active' : '' }}" title="Dashboard">
                        <i class="ti-home"></i> <span> Dashboard </span>
                    </a>
                </li>
                
                <!-- COMBINED MENU: STAFF & BIOMETRICS -->
                <li>
                    <a href="{{ route('employees.index') }}" class="waves-effect {{ request()->is('employees') ? 'mm active' : '' }}" title="Staff & Biometrics">
                        <i class="ti-id-badge"></i><span> Staff & Biometrics </span>
                    </a>
                </li>

                <li class="menu-title">Time & Attendance</li>
                <li>
                    <a href="{{ route('schedule.index') }}" class="waves-effect {{ request()->is('schedule') ? 'mm active' : '' }}" title="Schedule">
                        <i class="ti-time"></i> <span> Schedule </span>
                    </a>
                </li>
                
                <!-- UPDATED ATTENDANCE MENU WITH OVERTIME SUB-MENU -->
                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('check', 'sheet-report', 'overtime') ? 'active' : '' }}" title="Attendance">
                        <i class="ti-calendar"></i><span> Attendance </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-header">Attendance</li>
                        <li><a href="{{ route('check') }}" class="{{ request()->is('check') ? 'active' : '' }}">Today's Live Sheet</a></li>
                        <li><a href="{{ route('sheet-report') }}" class="{{ request()->is('sheet-report') ? 'active' : '' }}">Master Attendance</a></li>
                        <li><a href="{{ route('overtime') }}" class="{{ request()->is('overtime') ? 'active' : '' }}">Overtime Approvals</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('latetime') ? 'active' : '' }}" title="Time Exceptions">
                        <i class="dripicons-warning"></i><span> Time Exceptions </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-header">Time Exceptions</li>
                        <li><a href="{{ route('latetime') }}" class="{{ request()->is('latetime') ? 'active' : '' }}">Late Arrivals & Requests</a></li>
                    </ul>
                </li>

                <li class="menu-title">Reports & Payroll</li>
                <li>
                    <a href="{{ route('pay.report') }}" class="waves-effect {{ request()->is('pay-report') ? 'mm active' : '' }}" title="Pay Report">
                        <i class="ti-wallet"></i> <span> Pay Report </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}" class="waves-effect {{ request()->is('reports*') ? 'mm active' : '' }}" title="Reports Hub">
                        <i class="ti-stats-up"></i> <span> Reports Hub </span>
                    </a>
                </li>

                @if(auth()->user() && (auth()->user()->hasAnyRole(['admin', 'receptionist'])))
                <li class="menu-title">Visitors</li>
                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('visitor-checkin', 'visitor-logs') ? 'active' : '' }}" title="Visitor Management">
                        <i class="dripicons-user-group"></i><span> Visitor Management </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-header">Visitor Management</li>
                        <li><a href="{{ route('visitor.checkin') }}" class="{{ request()->is('visitor-checkin') ? 'active' : '' }}">Visitor Check-In</a></li>
                        <li><a href="{{ route('admin.visitor_index') }}" class="{{ request()->is('visitor-logs') ? 'active' : '' }}">Visitor Logs</a></li>
                    </ul>
                </li>
                @endif

                @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('devices.view') || auth()->user()->hasPermission('cameras.view') || auth()->user()->hasPermission('audit_logs.view') || auth()->user()->hasPermission('security.view'))
                <li class="menu-title">Administration</li>
                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('users*', 'roles*', 'finger_device*', 'cameras*', 'audit-logs*', 'security*') ? 'active' : '' }}" title="Admin & Security">
                        <i class="ti-shield"></i><span> Admin & Security </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-header">Admin & Security</li>
                        @if(auth()->user()->hasPermission('users.view'))
                            <li><a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">Users</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('roles.view'))
                            <li><a href="{{ route('roles.index') }}" class="{{ request()->is('roles*') ? 'active' : '' }}">Roles & Permissions</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('devices.view'))
                            <li><a href="{{ route('finger_device.index') }}" class="{{ request()->is('finger_device*') ? 'active' : '' }}">Devices</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('cameras.view'))
                            <li><a href="{{ route('cameras.index') }}" class="{{ request()->is('cameras*') ? 'active' : '' }}">Cameras</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('audit_logs.view'))
                            <li><a href="{{ route('audit_logs.index') }}" class="{{ request()->is('audit-logs*') ? 'active' : '' }}">Audit Logs</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('security.view'))
                            <li><a href="{{ route('security.dashboard') }}" class="{{ request()->is('security/dashboard') ? 'active' : '' }}">Security Dashboard</a></li>
                        @endif
                        @if(auth()->user()->hasPermission('security.manage'))
                            <li><a href="{{ route('security.settings') }}" class="{{ request()->is('security/settings') ? 'active' : '' }}">Security Settings</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <li class="menu-title">System</li>
                <li>
                    <a href="{{ route('admin.settings') }}" class="waves-effect {{ request()->is('settings') ? 'mm active' : '' }}" title="Settings">
                        <i class="ti-settings"></i> <span> Settings </span>
                    </a>
                </li>

            </ul>        
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Left Sidebar End -->