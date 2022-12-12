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
                            <h5 class="text-warning">Your Points: {{ Auth::user()->current_balance }}</h5>
                        </nav>
                    </div>
                </div>
            </div>
            <hr class="bg-body">
            <div class="mx-auto col-xl-3 col-md-6 col-sm-8 col-10 mt-10">
                <div class="card">
                    <div class="card-content">
                        <img src="assets/images/samples/motorcycle.jpg" class="card-img-top img-fluid"
                            alt="singleminded">
                        <div class="card-body">
                            <h3 class="card-title font-bold text-black">{{ $event->event_name }}</h3>
                            <p class="card-text font-bold text-black">
                                {{ \Carbon\Carbon::parse($event->fight_date_time)->isoFormat('dddd, MMMM DD, Y') }}
                            </p>
                            <p class="card-text font-bold text-black">
                                {{ $event->location }}
                            </p>
                            <div class="buttons">
                                <a href="#" class="btn btn-lg btn-outline-warning border-2">
                                    <span class="text-black font-bold">Enter Event</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    window.onload = function(){
        var tbl = new weirdTable('table');

        var _randomLoop = parseInt(Math.random() * 20);
        console.log(_randomLoop);

        var _resultList = [];

        for(var i = 0; i < _randomLoop; i++){
            _resultList.push(parseInt(Math.random() * 3));
        }

        tbl.addData(_resultList);
    }


    function weirdTable(tableId){
        var _me = null;
        
        var _currentIndex = 0;
        var _colCount     = 0;
        var _lastRowIndex = 0;
        var _lastResult = 0;
        
        var construct = function(tableId){
            _me = document.getElementById(tableId);
            _colCount = _me.rows[0].cells.length;
            _currentIndex = _colCount;
        };
        
        this.addData = function(data){

            var row = _me.rows[_lastRowIndex];
            
            //or var data = arguments;
            for(var i = 0; i < data.length; i++){

                console.log(data[i] + " == " + _lastResult);

                row = _me.insertRow(_lastRowIndex);

                if(data[i] == _lastResult){
                    console.log("InsertRow");
                    _lastRowIndex++;
                    row = _me.insertRow(_lastRowIndex);
                    for(var i = 1; i < _currentIndex; i++){
                        row.insertCell( i - 1 ).innerText = ' ';
                    }
                }else{
                    row.insertCell(_currentIndex).innerText = data[i];

_currentIndex++;
                }

                console.log("InsertCell");
                console.log("_currentIndex " + _currentIndex + " , " + _lastRowIndex);

                

                _lastResult = data[i];


                // if(_lastResult == data[i]){
                //     _currentIndex = 0;
                //     row = _me.insertRow(_lastRowIndex);
                //     console.log('inserRow');
                // }else{
                // }
            
                // console.log('insertCell');

                // row.insertCell(_currentIndex).innerText = data[i];
                // _lastResult = data[i];
                // console.log("last result " + _lastResult);
                // _currentIndex++;
                // _lastRowIndex++;


                // console.log(_currentIndex + " , " + _lastRowIndex)
            }
        };
        
        construct(tableId);
    }
    
</script>
@endpush