@extends('layouts.player')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title mx-md-5">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-first">
                        <h3 class="text-white">Today's Events</h3>
                    </div>
                    <div class="col-12 col-lg-6 order-md-2 order-last">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <h5 class="text-warning">Your Points: @money(Auth::user()->current_balance)</h5>
                        </nav>
                    </div>
                </div>
            </div>
            <hr class="bg-body">
            <div class="mx-auto col-xl-3 col-md-6 col-sm-8 col-10 mt-10">
                <div class="card">
                    <div class="card-content">
                        <img src="assets/images/samples/arena_poster.jpg" class="card-img-top img-fluid"
                            alt="singleminded">
                            @if(!empty($event->event_name))
                            <div class="card-body">
                                <h3 class="card-title font-bold text-black">{{ $event->event_name }}</h3>
                                <p class="card-text font-bold text-black text-sm">
                                    {{ \Carbon\Carbon::parse($event->fight_date_time)->isoFormat('dddd, MMMM DD, Y') }}
                                </p>
                                <p class="card-text font-bold text-black text-sm">
                                    {{ $event->location }}
                                </p>
                                <div class="buttons">
                                    <a href="{{ url('arena/'.$event->id) }}" class="btn btn-lg btn-outline-warning border-2">
                                        <span class="text-black font-bold">Enter Event</span>
                                    </a>
                                </div>
                            </div>

                            {{-- No event --}}

                            @else
                            <div class="card-body">
                                <h3 class="card-title font-bold text-black">NO EVENT YET</h3>
                                <p class="card-text font-bold text-black text-sm">
                                   Please wait......
                                </p>
                                <p class="card-text font-bold text-black text-sm">
                                    Looking for events......
                                </p>
                                <div class="buttons">
                                    <a href="#" class="disabled btn btn-lg btn-outline-warning border-2">
                                        <span class="text-black font-bold">Enter Event</span>
                                    </a>
                                </div>
                            </div>
                            @endif
                    </div>
                </div>
        </div>
    </div>
@endsection