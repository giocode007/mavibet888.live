@extends('layouts.master')
@section('menu')
@extends('sidebar.dashboard')
@endsection
@section('content')
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-9">
                {{-- Information Section --}}

                <div class="row">
                    <div class="col-lg">
                        <div class="card">
                            <div class="card-body bg-light-info">
                                <div class="row">
                                    <h6 class="font-bold">PLEASE TAKE NOTE OF YOUR REFFERAL LINK BELOW, ALL PLAYERS
                                        THAT WILL REGISTER UNDER THIS LINK WILL AUTOMATICALLY BE UNDER YOUR ACCOUNT.
                                        <div class="card-body">
                                            <div>
                                                <span class="text-danger">https://victory777.live/ACCOUNTID/{{ Auth::user()->player_code }}</span>
                                                <a href="javascript:void(0)" id="copy-code" class="py-2 btn badge bg-danger" data-id="https://victory777.live/ACCOUNTID/{{ Auth::user()->player_code }}">COPY REFERAL LINK</a>
                                            </div>
                                        </div>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-10">
                    <div class="col-12">
                        <div class="card-group">
                            <div class="card bg-primary">
                                <div class="card-content">
                                    <div class="card-body">
                                        <h4 class="card-title text-white">Current Wallet</h4>
                                        <p class="card-text text-white">
                                            Your points: @money(Auth::user()->current_balance)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-success">
                                <div class="card-content">
                                    <div class="card-body">
                                        <h4 class="card-title text-white">Current Commission ({{ Auth::user()->commission_percent * 100 }}%)</h4>
                                        <p class="card-text text-white">
                                            Your commission:  @commission(Auth::user()->current_commission)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profile modal readonly --}}
            <div class="col-12 col-lg-3">
                <div class="card" data-bs-toggle="modal" data-bs-target="#default">
                    <div class="card-body py-4 px-5">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl">
                                <img src="{{ URL::to('/images/'. Auth::user()->avatar) }}" alt="{{ Auth::user()->avatar }}">
                            </div>
                            <div class="ms-3 name">
                                <h5 class="font-bold">{{ Auth::user()->first_name }} , {{ Auth::user()->last_name }}</h5>
                                <h6 class="text-muted mb-0">{{ Auth::user()->user_name }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- user profile modal --}}
                <div class="card-body">
                    <!--Basic Modal -->
                    <div class="modal fade text-left" id="default" tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myModalLabel1">User Profile</h5>
                                    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Full Name</label>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group has-icon-left">
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control" name="fullName" value="{{ Auth::user()->first_name }} , {{ Auth::user()->last_name }}" readonly>
                                                        <div class="form-control-icon">
                                                            <i class="bi bi-person"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Email Address</label>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group has-icon-left">
                                                    <div class="position-relative">
                                                        <input type="email" class="form-control" name="email" value="{{ Auth::user()->email ?? 'No email' }}" readonly>
                                                        <div class="form-control-icon">
                                                            <i class="bi bi-envelope"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Mobile Number</label>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group has-icon-left">
                                                    <div class="position-relative">
                                                        <input type="number" class="form-control" name="phone_number" value="{{ Auth::user()->phone_number ?? 'No phone number'}}" readonly>
                                                        <div class="form-control-icon">
                                                            <i class="bi bi-phone"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                
                                            <div class="col-md-4">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group has-icon-left">
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control" value="{{ Auth::user()->status }}" readonly>
                                                        <div class="form-control-icon">
                                                            <i class="bi bi-bag-check"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Role Name</label>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group has-icon-left">
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control" value="{{ Auth::user()->role_type }}" readonly>
                                                        <div class="form-control-icon">
                                                            <i class="bi bi-exclude"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                                        <i class="bx bx-check d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Close</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- end user profile modal --}}

            </div>
        </section>
    </div>

</div>

@push('scripts')
    <script>

        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('body').on('click', '#copy-code', function () {
                var copyText =  $(this).data('id');

                // Copy the text inside the text field
                navigator.clipboard.writeText(copyText)
                .then(() => {
                    alert("Copied the text: " + copyText);
                })
                .catch(() => {
                    alert("something went wrong");
                });
            });

        });
  </script>
@endpush
@endsection