<div class="topbar">
    <div class="topbar-left">
        <a href="/" class="logo text-center d-flex align-items-center justify-content-center" style="height: 100%; text-decoration: none;">
            <span>
                <h3 style="font-weight: 700; margin: 0; font-size: 18px;">
                    <span style="color: #0EA5E9;">PRAGNAWARE</span> <span style="color: #22C55E;">SOLUTIONS</span>
                </h3>
            </span>
        </a>
    </div>

    <nav class="navbar-custom">
        <ul class="navbar-right d-flex list-inline float-right mb-0">
            <li class="dropdown notification-list d-none d-md-block">
                <a class="nav-link waves-effect" href="#" id="btn-fullscreen">
                    <i class="mdi mdi-fullscreen noti-icon" style="color: #64748B;"></i>
                </a>
            </li>

            <li class="dropdown notification-list">
                <div class="dropdown notification-list nav-pro-img">
                    <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <!-- இங்குதான் Asset Helper-ஐ இணைத்துள்ளேன் -->
                        <img src="{{ asset('assets/images/PG.png') }}" alt="user" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-power text-danger"></i> {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </li>
        </ul>

        <ul class="list-inline menu-left mb-0">
            <li class="float-left">
                <button class="button-menu-mobile open-left waves-effect">
                    <i class="mdi mdi-menu" style="color: #334155;"></i>
                </button>
            </li>
        </ul>
    </nav>
</div>