<style>
    /* Clean Hover & Active Fixes */
    #sidebar-menu ul li a:hover, #sidebar-menu ul li a:focus, #sidebar-menu ul li a:active {
        background-color: transparent !important; 
        color: #5867dd !important;
    }
    #sidebar-menu ul li a:hover i { color: #5867dd !important; }
    
    #sidebar-menu .submenu {
        background-color: #ffffff !important; 
        box-shadow: 0px 8px 25px rgba(0,0,0,0.15) !important;
        border: 1px solid #e2e8f0 !important;
        z-index: 9999 !important;
        border-radius: 8px;
    }
    
    body.enlarged #sidebar-menu ul li { position: relative !important; }
    .enlarged #sidebar-menu ul li:hover > a { background-color: #ffffff !important; color: #5867dd !important; }
    
    #sidebar-menu .submenu li a { padding: 10px 20px !important; transition: all 0.3s ease; }
    #sidebar-menu .submenu li a:hover { background-color: #f8f9fa !important; color: #5867dd !important; padding-left: 25px !important; }

    #sidebar-menu ul li a.active, #sidebar-menu ul li a.mm.active, .metismenu li.active > a, #sidebar-menu ul li.active > a {
        background-color: #e0e7ff !important; color: #4f46e5 !important;
    }
    #sidebar-menu ul li a.active i, #sidebar-menu ul li a.mm.active i, .metismenu li.active > a i, #sidebar-menu ul li.active > a i {
        color: #4f46e5 !important;
    }

    #sidebar-menu ul li a.has-arrow::after {
        content: "" !important; display: inline-block !important; float: right; margin-top: 8px;
        width: 7px; height: 7px; border-bottom: 2px solid #74788d; border-right: 2px solid #74788d;
        transform: rotate(-45deg); transition: transform 0.3s ease;
    }
    
    #sidebar-menu ul li.mm-active > a.has-arrow::after, #sidebar-menu ul li.active > a.has-arrow::after,
    #sidebar-menu ul li > a[aria-expanded="true"].has-arrow::after {
        transform: rotate(45deg) !important; border-color: #4f46e5 !important;
    }
</style>

<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                
                <li class="menu-title">Main</li>
                <li>
                    <a href="{{route('admin')}}" class="waves-effect {{ request()->is('admin') ? 'mm active' : '' }}">
                        <i class="ti-home"></i> <span> Dashboard </span>
                    </a>
                </li>
                
                <!-- COMBINED MENU: STAFF & BIOMETRICS -->
                <li>
                    <a href="/employees" class="waves-effect {{ request()->is('employees') ? 'mm active' : '' }}">
                        <i class="ti-id-badge"></i><span> Staff & Biometrics </span>
                    </a>
                </li>

                <li class="menu-title">Time & Attendance</li>
                <li>
                    <a href="/schedule" class="waves-effect {{ request()->is('schedule') ? 'mm active' : '' }}">
                        <i class="ti-time"></i> <span> Schedule </span>
                    </a>
                </li>
                
                <!-- UPDATED ATTENDANCE MENU WITH OVERTIME SUB-MENU -->
                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('check', 'sheet-report', 'overtime') ? 'active' : '' }}">
                        <i class="ti-calendar"></i><span> Attendance </span>
                    </a>
                    <ul class="submenu">
                        <li><a href="/check" class="{{ request()->is('check') ? 'active' : '' }}">Today's Live Sheet</a></li>
                        <li><a href="/sheet-report" class="{{ request()->is('sheet-report') ? 'active' : '' }}">Master Attendance</a></li>
                        <li><a href="/overtime" class="{{ request()->is('overtime') ? 'active' : '' }}">Overtime Approvals</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('latetime') ? 'active' : '' }}">
                        <i class="dripicons-warning"></i><span> Time Exceptions </span>
                    </a>
                    <ul class="submenu">
                        <li><a href="/latetime" class="{{ request()->is('latetime') ? 'active' : '' }}">Late Arrivals & Requests</a></li>
                    </ul>
                </li>

                <li class="menu-title">Reports & Payroll</li>
                <li>
                    <a href="/pay-report" class="waves-effect {{ request()->is('pay-report') ? 'mm active' : '' }}">
                        <i class="ti-wallet"></i> <span> Pay Report </span>
                    </a>
                </li>

                <li class="menu-title">Visitors</li>
                <li>
                    <a href="javascript:void(0);" class="waves-effect has-arrow {{ request()->is('visitor-checkin', 'visitor-logs') ? 'active' : '' }}">
                        <i class="dripicons-user-group"></i><span> Visitor Management </span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('visitor.checkin') }}" class="{{ request()->is('visitor-checkin') ? 'active' : '' }}">Visitor Check-In</a></li>
                        <li><a href="{{ url('/visitor-logs') }}" class="{{ request()->is('visitor-logs') ? 'active' : '' }}">Visitor Logs</a></li>
                    </ul>
                </li>

                <li class="menu-title">System</li>
                <li>
                    <a href="/settings" class="waves-effect {{ request()->is('settings') ? 'mm active' : '' }}">
                        <i class="ti-settings"></i> <span> Settings </span>
                    </a>
                </li>

            </ul>        
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Left Sidebar End -->