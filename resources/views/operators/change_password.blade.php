@extends('layouts.player')
@section('content')
<div id="main">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3 class="text-white">Change Profile Information</h3>
            </div>
        </div>
    </div>
    
    {{-- message --}}
    {!! Toastr::message() !!}

    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <br>
                    @if (Auth::user()->role_type == 'Operator')
                    <form method="POST" action="{{ route('changeProfileInfo') }}" class="md-float-material">
                    @elseif (Auth::user()->role_type == 'Declarator')
                    <form method="POST" action="{{ route('declarator/changeProfileInfo') }}" class="md-float-material">
                    @endif
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-lg @error('last_name') is-invalid @enderror" name="last_name" value="{{ $data[0]->last_name }}" placeholder="Enter Your Last Name">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-lg @error('first_name') is-invalid @enderror" name="first_name" value="{{ $data[0]->first_name }}" placeholder="Enter Your First Name">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="number" class="form-control form-control-lg @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ $data[0]->phone_number }}" placeholder="Enter Your Phone Number">
                            <div class="form-control-icon">
                                <i class="bi bi-phone"></i>
                            </div>
                            @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-lg @error('current_password') is-invalid @enderror" 
                            name="current_password" value="{{ old('current_password') }}" placeholder="Enter Old Password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            @error('current_password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                            name="password" placeholder="Enter Current Password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-lg" name="password_confirmation" placeholder="Choose Confirm Password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Change Password</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection