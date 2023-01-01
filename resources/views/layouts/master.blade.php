<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>mavibet888</title>

    <link rel="shortcut icon" href="{{ URL::to('assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ URL::to('assets/vendors/iconly/bold.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/vendors/simple-datatables/style.css') }}">

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

    <link rel="stylesheet" href="{{ URL::to('assets/css/app.css') }}">
    <link rel="shortcut icon" href="{{ URL::to('assets/images/favicon.svg') }}" type="image/x-icon">

     {{-- message toastr --}}
     <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css"> 
     <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>

</head>
<style>
    .form-group[class*=has-icon-].has-icon-left .form-select {
    padding-left: 2.5rem;
}
</style>

<body>
    <div id="app">
        @yield('menu')
        {{-- content main page --}}
        @yield('content')
       
    </div>

    <script src="{{ URL::to('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::to('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>

    <script src="{{ URL::to('assets/js/main.js') }}"></script>
@stack('scripts')
</body>

</html>