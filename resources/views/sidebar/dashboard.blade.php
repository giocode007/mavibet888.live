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

                <li class="sidebar-title">Menu</li>
                <li class="sidebar-item active">
                    <a href="{{ route('home') }}" class='sidebar-link'>
                        <i class="bi bi-speedometer"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('summary_report') }}" class='sidebar-link'>
                        <i class="bi bi-speedometer"></i>
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
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Commision Withdrawal</span>
                    </a>
                </li>

                <li class="sidebar-title">Player Controller</li>
                <li class="sidebar-item  has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-diagram-3-fill"></i>
                        <span class="font-bold">Players</span>
                    </a>
                    <ul class="submenu">
                        @if (Auth::user()->role_type != 'Gold_Agent')
                            <li class="submenu-item active">
                                <a href="{{ route('getMyAgents') }}">Agents</a>
                            </li>
                        @endif
                        <li class="submenu-item active">
                            <a href="{{ route('getActivePlayers') }}">Active Players</a>
                        </li>
                        <li class="submenu-item active">
                            <a href="{{ route('getDeletedPlayers') }}">Approval Players</a>
                        </li>
                        
                    </ul>
                </li>

                <li class="sidebar-title">Settings</li>

                <li class="sidebar-item">
                    <a href="{{ url('agent/profile/'.Auth::user()->id) }}" class='sidebar-link'>
                        <i class="bi bi-shield-lock"></i>
                        <span>Change Password</span>
                    </a>
                </li>
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