<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>mavibet888.com</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ URL::to('assets/vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/vendors/simple-datatables/style.css') }}">

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

    {{-- message toastr --}}
    <link rel="stylesheet" href="https://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css"> 
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>

    <link rel="stylesheet" href="{{ URL::to('assets/css/app-player.css') }}">
    <style>
        .blink {
            animation: blinker 1.5s linear infinite;
            color: red;
            font-family: sans-serif;
        }
        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>
    @stack('style')
</head>

<body>
    <div id="app">
        <div id="main" class='layout-navbar'>
            <header class='mb-3'>
                <nav class="navbar navbar-expand navbar-light ">
                    <div class="container-fluid mx-sm-5">
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            @if (Auth::user()->role_type=='Player')
                            <a class="font-bold text-white" href="{{ route('player') }}"> Home</a>
                            @elseif (Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Loader')
                            <a class="font-bold text-white" href="{{ route('admin') }}"> Home</a>
                            @elseif (Auth::user()->role_type == 'Declarator')
                            <a class="font-bold text-white" href="{{ route('declarator') }}"> Home</a>
                            @endif
                            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                                @if (Auth::user()->role_type=='Operator' || Auth::user()->role_type == 'Loader' )
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link active dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span class="text-white font-bold">ADMIN</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <h6 class="dropdown-header">Admin Reports</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="/events"><i class="icon-mid bi bi-file-earmark-text me-2"></i>
                                            Events</a></li>
                                        <li><a class="dropdown-item" href="{{ route('getAgents') }}"><i class="icon-mid bi bi-people-fill me-2"></i>
                                            Users</a></li>
                                        <hr class="dropdown-divider">
                                        <li>
                                            <a href="{{ route('getAudit') }}" class="dropdown-item">
                                                <i class="icon-mid bi bi-file-earmark-spreadsheet-fill me-2"></i> Audit Trail
                                            </a>
                                        </li>
                                    </ul>
                                </li> 
                                @endif
                                @if (Auth::user()->role_type=='Declarator')
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link active dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span class="text-white font-bold">DECLARATOR</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <h6 class="dropdown-header">Declarator Reports</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="/events"><i class="icon-mid bi bi-file-earmark-text me-2"></i>
                                            Events</a></li>
                                    </ul>
                                </li> 
                                @endif
                            </ul>
                            <div class="dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="user-menu d-flex">
                                        <div class="user-name text-end me-3 align-self-center">
                                            <h6 class="mb-0 text-white ">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h6>
                                        </div>
                                        <div class="user-img d-flex align-items-center">
                                            <div class="avatar avatar-md">
                                                <img src="{{ URL::to('/images/'. Auth::user()->avatar) }}" alt="{{ Auth::user()->avatar }}">
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <h6 class="dropdown-header">Hello, {{ Auth::user()->first_name }}</h6>
                                    </li>
                                    @if (Auth::user()->role_type=='Player')
                                    <li><a class="dropdown-item" href="{{ route('player') }}"><i class="icon-mid bi bi-house-door-fill me-2"></i>
                                        Home</a></li>
                                    <li><a class="dropdown-item" href="{{ route('getPlayerBettingHistory') }}"><i class="icon-mid bi bi-cash-stack me-2"></i>
                                        Betting History</a></li>
                                    <li><a class="dropdown-item" href="{{ route('getPlayerTransactionHistory') }}"><i class="icon-mid bi bi-calendar-week-fill me-2"></i> 
                                        Transaction History</a></li>
                                    <li>
                                    <li><a class="dropdown-item" href="{{ url('profile/settings/'.Auth::user()->id) }}"><i class="icon-mid bi bi-gear-fill me-2"></i>
                                        Profile Settings</a></li>
                                        <hr class="dropdown-divider">
                                    </li> 
                                    @elseif (Auth::user()->role_type=='Operator' || Auth::user()->role_type == 'Loader')
                                    <li><a class="dropdown-item" href="{{ url('history/'.Auth::user()->id) }}"><i class="icon-mid bi bi-cash-stack me-2"></i>
                                        My Loadings</a></li>
                                    <li><a class="dropdown-item" href="{{ url('profile/'.Auth::user()->id) }}"><i class="icon-mid bi bi-gear-fill me-2"></i>
                                        Profile Settings</a></li>
                                        <hr class="dropdown-divider">
                                    </li> 
                                    @elseif (Auth::user()->role_type=='Declarator')
                                    <li><a class="dropdown-item" href="{{ url('declarator/profile/'.Auth::user()->id) }}"><i class="icon-mid bi bi-gear-fill me-2"></i>
                                        Profile Settings</a></li>
                                        <hr class="dropdown-divider">
                                    </li> 
                                    @elseif (Auth::user()->role_type=='Sub_Operator' 
                                    || Auth::user()->role_type=='Master_Agent' 
                                    || Auth::user()->role_type=='Gold_Agent' )
                                    <li><a class="dropdown-item" href="{{ route('home') }}"><i class="icon-mid bi bi-house-door-fill me-2"></i>
                                        Home</a></li>
                                        <hr class="dropdown-divider">
                                    </li> 
                                    @endif
                                    
                                    <li>
                                        <a href="{{ route('logout') }}" class="dropdown-item">
                                            <i class="icon-mid bi bi-box-arrow-right me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>
            @yield('content')
        </div>
    </div>

    

    <script src="{{ URL::to('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::to('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
@stack('scripts')
</body>

</html>