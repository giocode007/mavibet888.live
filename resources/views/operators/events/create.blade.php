@extends('layouts.player')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
<div id="main">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-first">
                <h3 class="text-white">ADD NEW EVENT</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-last">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('events') }}">Events</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    
    {{-- message --}}
    {!! Toastr::message() !!}

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Input Information</h4>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <form class="form form-horizontal" action="{{ route('events/save') }}" method="POST">
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="font-bold">EVENT NAME</label>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <input type="text" class="form-control @error('event_name') is-invalid @enderror" value="{{ old('event_name') }}"
                                                placeholder="enter event name" name="event_name">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="font-bold">DATE OF FIGHT</label>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <input type="datetime-local" class="form-control @error('fight_date_time') is-invalid @enderror" value="{{ old('fight_date_time') }}"
                                            placeholder="{{ __('enter data of fight') }}" name="fight_date_time">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="font-bold">LOCATION</label>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <input type="text" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}"
                                                placeholder="enter location" name="location">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="font-bold">VIDEO CODE</label>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <input type="text" class="form-control @error('video_code') is-invalid @enderror" value="{{ old('video_code') }}"
                                                placeholder="enter video code" name="video_code">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="font-bold">PALASADA</label>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <input type="text" class="form-control @error('palasada') is-invalid @enderror" value="{{ old('palasada') }}"
                                                placeholder="enter palasada" name="palasada">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2 mb-1">Save</button>
                                    <a href="{{ route('events') }}"><button type="button" class="btn btn-light-secondary me-2 mb-1">Cancel</button></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection