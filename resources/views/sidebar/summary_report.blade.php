<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-item">
                    <div class="card-body">
                        <div class="badges">
                            <span>Your code:</span>
                            <span class="badge bg-success">{{ Auth::user()->agent_code }}</span>
                        </div>
                    </div>
                </li>

                <li class="sidebar-title">Menu</li>
                <li class="sidebar-item">
                    <a href="{{ route('home') }}" class='sidebar-link'>
                        <i class="bi bi-speedometer"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item active">
                    <a href="{{ route('summary_report') }}" class='sidebar-link'>
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Summary Report</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('load_logs') }}" class='sidebar-link'>
                        <i class="bi bi-clock-history"></i>
                        <span>Load Logs</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('commission_logs') }}" class='sidebar-link'>
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Commision Logs</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('commission_withdrawal') }}" class='sidebar-link'>
                        <i class="bi bi-wallet"></i>
                        <span>Commision Withdrawal</span>
                    </a>
                </li>

               

                {{-- @if (Auth::user()->role_name=='Admin')
                    <li class="sidebar-title">Page &amp; Controller</li>
                    <li class="sidebar-item  has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-hexagon-fill"></i>
                            <span>Maintenain</span>
                        </a>
                        <ul class="submenu">
                            <li class="submenu-item">
                                <a href="{{ route('userManagement') }}">User Control</a>
                            </li>
                            <li class="submenu-item">
                                <a href="{{ route('activity/log') }}">User Activity Log</a>
                            </li>
                            <li class="submenu-item">
                                <a href="{{ route('activity/login/logout') }}">Activity Log</a>
                            </li>
                        </ul>
                    </li>
                @endif --}}
                
                <li class="sidebar-item  has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-diagram-3-fill"></i>
                        <span class="font-bold">Players</span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item active">
                            <a href="{{ route('form/staff/new') }}">Agents</a>
                        </li>
                        <li class="submenu-item active">
                            <a href="{{ route('form/staff/new') }}">Active Players</a>
                        </li>
                        <li class="submenu-item active">
                            <a href="{{ route('form/staff/new') }}">Approval Players</a>
                        </li>
                        <li class="submenu-item active">
                            <a href="{{ route('form/staff/new') }}">Deleted Players</a>
                        </li>
                    </ul>
                </li>
                {{-- <li class="sidebar-item  has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>View Record</span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item">
                            <a href="{{ route('form/view/detail') }}">View Detail</a>
                        </li>
                    </ul>
                </li> --}}

                <li class="sidebar-title">Settings</li>

                <li class="sidebar-item">
                    <a href="{{ route('change/password') }}" class='sidebar-link'>
                        <i class="bi bi-shield-lock"></i>
                        <span>Change Password</span>
                    </a>
                </li>
                
                {{-- <li class="sidebar-item">
                    <a href="{{ route('lock_screen') }}" class='sidebar-link'>
                        <i class="bi bi-lock-fill"></i>
                        <span>Lock Screen</span>
                    </a>
                </li> --}}

                <li class="sidebar-item">
                    <a href="{{ route('logout') }}" class='sidebar-link'>
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log Out</span>
                    </a>
                </li>
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>