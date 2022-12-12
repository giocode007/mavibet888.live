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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

    {{-- message toastr --}}
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css"> 
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>

    <link rel="stylesheet" href="{{ URL::to('assets/css/app-player.css') }}">

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
                            @elseif (Auth::user()->role_type=='Operator')
                            <a class="font-bold text-white" href="{{ route('admin') }}"> Home</a>
                            @endif
                            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                                {{-- {{-- <li class="nav-item dropdown me-1">
                                    <a class="nav-link active dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class='bi bi-envelope bi-sub fs-4 text-gray-600'></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <h6 class="dropdown-header">Mail</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#">No new mail</a></li>
                                    </ul>
                                </li> --}}
                                @if (Auth::user()->role_type=='Operator')
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
                                        <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-people-fill me-2"></i>
                                            Users</a></li>
                                        <hr class="dropdown-divider">
                                        <li>
                                            <a href="#" class="dropdown-item">
                                                <i class="icon-mid bi bi-file-earmark-spreadsheet-fill me-2"></i> Audit Trail
                                            </a>
                                        </li>
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
                                    <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-cash-stack me-2"></i>
                                        Betting History</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-calendar-week-fill me-2"></i> 
                                        Transaction History</a></li>
                                    <li>
                                    @endif
                                    @if (Auth::user()->role_type=='Operator')
                                    <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-cash-stack me-2"></i>
                                        My Loadings</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('change/password') }}"><i class="icon-mid bi bi-gear-fill me-2"></i>
                                        Change Password</a></li>
                                        <hr class="dropdown-divider">
                                    </li>   
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
            <div id="main">
                <div class="page-heading">
                    <h5 class="text-white">Event name - Date</h5>
                </div>
                <div class="page-content">
                    <section class="row">
                        <div class="col-12 col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Header Card</h4>
                                </div>
                                <div class="card-body">
                                    Body Card
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="card bg-warning">
                                <div class="text-center p-1">
                                    <span class="text-black font-bold text-uppercase">Payout with 120 and below shall be cancelled</span>
                                </div>
                            </div>
        
                            <div class="wrapper">
                                <div class="box betting">BETTING</div>
                                <div class="box result">FIGHT #<span class="bg-success p-1"> 151 : MERON </span></div>
                                <div class="box status"><span class="bg-success p-1">OPEN</span></div>
                                <div class="box fight">FIGHT #<span> 152 </span></div>
                                <div class="box-meron meron">MERON</div>
                                <div class="box-wala wala">WALA</span></div>
        
                                <div class="box d-meron">
                                  <div class="box-money meron-total-bet text-warning">100,100</div>
                                  <div class="box-money meron-payout">PAYOUT = <span> 180.0 </span> </div>
                                  <div class="box-money meron-bet text-success"> 10,000 </div>
                                  <div class="box-money meron-reward text-warning"> 18,000 </div>
                                  <div class="box-bet-meron bet-meron"><i class="icon-mid bi bi-plus-circle me-1"></i>BET MERON</div>
                                </div>
        
                                <div class="box d-wala">
                                    <div class="box-money wala-total-bet text-warning">100,100</div>
                                    <div class="box-money wala-payout">PAYOUT = <span> 180.0 </span> </div>
                                    <div class="box-money wala-bet text-success"> 10,000 </div>
                                    <div class="box-money wala-reward text-warning"> 18,000 </div>
                                    <div class="box-bet-wala bet-wala"><i class="icon-mid bi bi-plus-circle me-1"></i>BET WALA</span></div>
                                </div>
        
                                <div class="box-points points">Current Points: <span class="text-warning">100.00</span></div>
                                <div class="box-amount amount">
                                    <form id="form">
                                        <input id="input-bet" type="number" class="form-control form-control-lg" placeholder="ENTER BET AMOUNT"></div>
                                    </form>
                                <div class="d-choices">
                                    <div class="box-choices bet-50">50</span></div>
                                    <div class="box-choices bet-100">100</span></div>
                                    <div class="box-choices bet-500">500</span></div>
                                    <div class="box-choices bet-1k">1K</span></div>
                                    <div class="box-choices bet-2k">2K</span></div>
                                    <div class="box-choices bet-5k">5K</span></div>
                                    <div class="box-choices bet-10k">10K</span></div>
                                    <div class="box-choices bet-max">MAX</span></div>
                                    <div class="box-choices bet-clear">CLEAR</span></div>
                                </div>
        
                                <div class="box-draw total-draw text-success">TOTAL DRAW</div>
                                <div class="box-draw total-draw-amount">0</div>
        
                                <div class="box-bet-draw bet-draw"><i class="icon-mid bi bi-plus-circle me-1"></i>BET DRAW</span></div>
                                <div class="box-draw draw-amount">0</div>
        
                                <div class="box-draw-max draw-max-amount">DRAW WINS X 8. Max. DRAW bet 1000/fight</span></div>
        
        
                            </div>
        
                            
                            {{-- <div class="card">
                                <div class="card-header">
                                    <h4>Visitors Profile</h4>
                                </div>
                                <div class="card-body">
                                    <div id="chart-visitors-profile"></div>
                                </div>
                            </div> --}}
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    

    <script src="{{ URL::to('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::to('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>

    <script src="{{ mix('js/app.js') }}"></script>    

</body>

</html>