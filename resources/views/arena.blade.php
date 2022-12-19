@extends('layouts.player')
@section('content')
    <div id="main">
        <div class="page-heading">
            <h5 class="text-warning">{{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('dddd') }} {{ $event[0]->event_name }} - {{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('MM/DD/Y') }}</h5>
        </div>
        <div class="page-content">
            <section class="row">
                <div class="col-12 col-lg-6">
                    <iframe class="live-video" width="100%" height="500"
                    src="{{ $event[0]->video_code }}" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen></iframe>

                    @if (Auth::user()->role_type=='Operator')
                    <div class="py-2">
                        <select class="form-control" id="status" name="status">
                            <option>Select Status</option>
                            @foreach ($status as $key => $value)
                                <option value="{{ $key }}" {{ ( $key == $selectedStatus[0]->id) ? 'selected' : '' }}> 
                                    {{ $value }} 
                                </option>
                            @endforeach    
                        </select>
                    </div>

                    <div class="d-flex justify-content-around py-2 border">
                        <div class="align-self-sm-center">
                            <span class="text-white font-bold">BETTING STATUS : </span>
                        </div>
                        <div>
                            <button id="1" value="{{ $fight[0]->id }}" class="fight-status bg-primary p-2 text-white">OPEN</button>
                            <button id="0" value="{{ $fight[0]->id }}" class="fight-status bg-danger p-2 text-white">CLOSE</button>
                        </div>
                    </div>
                    @if ($fight[0]->isOpen == 0)
                        <div id="declare-result" class="d-flex justify-content-around border py-2">
                            <div class="align-self-sm-center text-white font-bold">
                                DECLARE RESULT :
                            </div>
                            <div>
                                <button value="meron" class="buttonResult bg-danger p-2 text-white">MERON</button>
                                <button value="wala" class="buttonResult bg-primary p-2 text-white">WALA</button>
                                <button value="draw" class="buttonResult bg-success p-2 text-white">DRAW</button>
                                <button value="cancel" class="buttonResult bg-light-secondary p-2 font-bold">CANCEL</button>
                            </div>
                        </div>
                    @endif
                    
                    <div id="declare-result" class="hide justify-content-around border py-2">
                        <div class="align-self-sm-center text-white font-bold">
                            DECLARE RESULT :
                        </div>
                        <div>
                            <button value="meron" class="buttonResult bg-danger p-2 text-white">MERON</button>
                            <button value="wala" class="buttonResult bg-primary p-2 text-white">WALA</button>
                            <button value="draw" class="buttonResult bg-success p-2 text-white">DRAW</button>
                            <button value="cancel" class="buttonResult bg-light-secondary p-2 font-bold">CANCEL</button>
                        </div>
                    </div>

                    @if ($fight[0]->result != NULL)
                    <div id="nextFight" class="d-flex justify-content-around border py-2">
                        <div class="align-self-sm-center text-white font-bold">
                            NEXT FIGHT : 
                        </div>
                        <div>
                            <button id="reset-all" class="bg-warning p-2 font-bold">RESET ALL</button>
                            <button id="resetting" class="hide bg-warning p-2 font-bold"><span class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> RESETTING...</button>
                            <button id="go-next" class="hide bg-info p-2 font-bold">GO NEXT</button>
                        </div>
                    </div>
                    @endif

                    <div id="nextFight" class="hide justify-content-around border py-2">
                        <div class="align-self-sm-center text-white font-bold">
                            NEXT FIGHT : 
                        </div>
                        <div>
                            <button id="reset-all" class="bg-warning p-2 font-bold">RESET ALL</button>
                            <button id="resetting" class="hide bg-warning p-2 font-bold"><span class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> RESETTING...</button>
                            <button id="go-next" class="hide bg-info p-2 font-bold">GO NEXT</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-around border py-2">
                        <div class="align-self-sm-center text-white font-bold">
                            REFRESH: 
                        </div>
                        <div>
                            <button class="bg-success p-2 text-white">REFRESH USERS</button>
                        </div>
                    </div>

                    @endif

                    
                </div>
                <div class="col-12 col-lg-6">
                    <div class="blink card bg-warning">
                        <div class="text-center p-1">
                            <span id="spanStatus" class="text-black font-bold text-uppercase">{{ $selectedStatus[0]->status_type }}</span>
                        </div>
                    </div>
                    <div class="wrapper">
                        <input id="fightId" type="hidden" value="{{ $fight[0]->id }}">
                        <input id="fightNumber" type="hidden" value="{{ $fight[0]->fight_number }}">
                        <input id="eventId" type="hidden" value="{{ $event[0]->id }}">
                        <input id="userId" type="hidden" value="{{ Auth::user()->id }}">
                        <div class="box betting">BETTING</div>
                        <div class="box result">LAST FIGHT #
                            @if(!empty($lastFight->result))
                                @if ($lastFight->result == 'meron')
                                <span id="spanLastResult" class="bg-danger p-1"> {{ $lastFight->fight_number }} : MERON </span>
                                @elseif ($lastFight->result == 'wala')
                                <span id="spanLastResult" class="bg-primary p-1"> {{ $lastFight->fight_number }}: WALA </span>
                                @elseif ($lastFight->result == 'draw')
                                <span id="spanLastResult" class="bg-success p-1"> {{ $lastFight->fight_number }}: WALA </span>
                                @elseif ($lastFight->result == 'cancel')
                                <span id="spanLastResult" class="bg-light-secondary p-1"> {{ $lastFight->fight_number }}: CANCEL </span>
                                @endif
                            @else
                            <span id="spanLastResult" class="p-1"> 0 </span>
                            @endif
                        </div>
                        <div class="box status">
                            @if ($fight[0]->isOpen == 0)
                                <span id="isOpen" class="bg-danger p-1">
                                    CLOSE
                                </span>
                            @else
                                <span id="isOpen" class="bg-success p-1">
                                    OPEN
                                </span>
                            @endif
                        </div>
                        <div class="box fight">CURRENT FIGHT #
                            @if(!empty($fight[0]->result))
                                @if ($fight[0]->result == 'meron')
                                <span id="spanResult" class="bg-danger p-1"> {{ $fight[0]->fight_number }} MERON </span>
                                @elseif ($fight[0]->result == 'wala')
                                <span id="spanResult" class="bg-primary p-1"> {{ $fight[0]->fight_number }} WALA </span>
                                @elseif ($fight[0]->result == 'draw')
                                <span id="spanResult" class="bg-success p-1"> {{ $fight[0]->fight_number }} WALA </span>
                                @elseif ($fight[0]->result == 'cancel')
                                <span id="spanResult" class="bg-light-secondary p-1"> {{ $fight[0]->fight_number }} CANCEL </span>
                                @endif
                            @else
                            <span id="spanResult"> {{ $fight[0]->fight_number }} </span>
                            @endif
                        </div>
                        <div class="box-meron meron">MERON</div>
                        <div class="box-wala wala">WALA</span></div>

                        <div class="box d-meron">
                            <div class="box-money meron-total-bet text-warning"> <span id="totalMeronBet" > @comma($allMeronBet) </span>  </div>
                            <div class="box-money meron-payout">PAYOUT = <span id="meronPayout" > @payout($meronPayout) </span> </div>
                            <div id="meronBet" class="box-money meron-bet text-success"> @comma($meronBet) </div>
                            <div class="box-money meron-reward text-warning"><span id="spanMeronReward"> @comma($meronBet * ($meronPayout / 100)) </span></div>
                            @if ($fight[0]->isOpen == 0)
                                <div id="meron1" class="bet-meron box-bet-closed"><i class="icon-mid bi bi-plus-circle me-1"></i>BET MERON</div>
                                <div id="meron" class="bet-meron player-bet box-bet-meron hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET MERON</div>
                            @else
                                <div id="meron1" class="bet-meron box-bet-closed hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET MERON</div>
                                <div id="meron" class="bet-meron player-bet box-bet-meron"><i class="icon-mid bi bi-plus-circle me-1"></i>BET MERON</div>
                            @endif
                        </div>

                        <div class="box d-wala">
                            <div class="box-money wala-total-bet text-warning"> <span id="totalWalaBet" > @comma($allWalaBet) </span></div>
                            <div class="box-money wala-payout">PAYOUT = <span id="walaPayout"> @payout($walaPayout) </span> </div>
                            <div id="walaBet" class="box-money wala-bet text-success"> @comma($walaBet) </div>
                            <div class="box-money wala-reward text-warning"><span id="spanWalaReward"> @comma($walaBet * ($walaPayout / 100)) </span></div>
                            @if ($fight[0]->isOpen == 0)
                                <div id="wala1" class="box-bet-closed bet-wala"><i class="icon-mid bi bi-plus-circle me-1"></i>BET WALA</span></div>
                                <div id="wala" class="player-bet box-bet-wala bet-wala hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET WALA</span></div>
                            @else
                                <div id="wala1" class="box-bet-closed bet-wala hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET WALA</span></div>
                                <div id="wala" class="player-bet box-bet-wala bet-wala"><i class="icon-mid bi bi-plus-circle me-1"></i>BET WALA</span></div>
                            @endif
                        </div>
                       
                        <div class="box-points points">Current Points: <span id="current_balance" class="text-warning">@money(Auth::user()->current_balance )</span></div>
                        <div class="box-amount amount">
                                <input id="bet-amount" type="number" class="form-control form-control-lg" placeholder="ENTER BET AMOUNT"></div>
                        <div class="d-choices">
                            {{-- <div id="btn1" value="50" class="button-amount box-choices bet-50">50</span></div> --}}
                            <button id="btn1" value="50" class="box-choices bet-50">50</button>
                            <button id="btn2" value="100" class="box-choices bet-100">100</button>
                            <button id="btn3" value="500" class="box-choices bet-500">500</button>
                            <button id="btn4" value="1000" class="box-choices bet-1k">1K</button>
                            <button id="btn5" value="2000" class="box-choices bet-2k">2K</button>
                            <button id="btn6" value="5000" class="box-choices bet-5k">5K</button>
                            <button id="btn7" value="10000" class="box-choices bet-10k">10K</button>
                            <button id="btn9" class="box-choices bet-clear">CLEAR</button>
                        </div>

                        <div class="box-draw total-draw text-success">TOTAL DRAW</div>
                        <div class="box-draw total-draw-amount"> <span id="totalDrawBet" > @comma($allDrawBet) </span></div>
                        @if ($fight[0]->isOpen == 0)
                            <div id="draw1" class="box-bet-closed bet-draw"><i class="icon-mid bi bi-plus-circle me-1"></i>BET DRAW</span></div>
                            <div id="draw" class="player-bet box-bet-draw bet-draw hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET DRAW</span></div>
                        @else
                            <div id="draw1" class="box-bet-closed bet-draw hide"><i class="icon-mid bi bi-plus-circle me-1"></i>BET DRAW</span></div>
                            <div id="draw" class="player-bet box-bet-draw bet-draw"><i class="icon-mid bi bi-plus-circle me-1"></i>BET DRAW</span></div>
                        @endif
                        <div id="draw-amount-bet" class="box-draw draw-amount"> @comma($drawBet) </div>

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

@push('scripts')

<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('body').on('click', '#reset-all', function () {
        var id = document.getElementById("fightId").value;
        document.getElementById('reset-all').classList.add('hide');
        document.getElementById('resetting').classList.remove('hide');


        $.ajax({
        url: "{{ url('checkFight') }}",
        type: "GET",
        data: {id:id},
            success: function (response) {
                if(response.result != null){
                    $.ajax({
                    url: "{{ url('resetAll') }}",
                    type: "GET",
                    data: {id:id},
                        success: function (response) {
                            document.getElementById('reset-all').classList.remove('hide');
                            document.getElementById('resetting').classList.add('hide');
                            document.getElementById('go-next').classList.remove('hide');
                        },
                        error: function (response) {
                            console.log('Error:', response);
                        }
                    });
                }
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
    })


    $('body').on('click', '#go-next', function () {
        var id = document.getElementById("fightId").value;
        var fightNumber = document.getElementById("fightNumber").value;
        var eventId = document.getElementById("eventId").value;

        $.ajax({
        url: "{{ url('goNext') }}",
        type: "GET",
        data: {id:id,fightNumber:fightNumber,eventId:eventId},
            success: function (response) {
                if(response.success){

                    console.log(response[0]);

                    document.getElementById('nextFight').classList.remove('d-flex');
                    document.getElementById('nextFight').classList.add('hide');
                    document.getElementById('go-next').classList.add('hide');

                    document.getElementById("fightId").value = response[0];
                    document.getElementById("fightNumber").value = parseInt(fightNumber) + 1;
                }
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
    })


    var resultButton = document.querySelectorAll(".buttonResult");
    resultButton.forEach(function(btn) {
        btn.addEventListener("click", function(e) {
        
        var result = this.value;
        var payoutMeron = document.getElementById("meronPayout").textContent / 100;
        var payoutWala = document.getElementById("walaPayout").textContent / 100;
        var id = document.getElementById("fightId").value;

            $.ajax({
            url: "{{ url('checkFight') }}",
            type: "GET",
            data: {id:id},
                success: function (response) {
                    if(response.isOpen == 0){
                        if (confirm("Are you sure you want to set the result to " + result.toUpperCase() + "?")) 
                            {
                                $.ajax({
                                    url: "{{ route('fightResult') }}",
                                    type: "GET",
                                    data: {id:id,result:result,payoutMeron:payoutMeron,payoutWala:payoutWala},
                                    success: function (response) {
                                        document.getElementById('nextFight').classList.remove('hide');
                                        document.getElementById('nextFight').classList.add('d-flex');
                                    },
                                    error: function (response) {
                                        console.log('Error:', response);
                                    }
                                });
                            }
                    }
                },
                error: function (response) {
                    console.log('Error:', response);
                }
            });
        });
    });


    var fightButton = document.querySelectorAll(".fight-status");
    fightButton.forEach(function(btn) {
        btn.addEventListener("click", function(e) {
            let status = this.id;
            let id = this.value;
            
            $.ajax({
            url: "{{ url('fightStatus') }}",
            type: "GET",
            data: {id:id,status:status},
                success: function (response) {
                    if(response.isOpen == 0)
                    {
                        document.getElementById('declare-result').classList.add('d-flex');
                        document.getElementById('declare-result').classList.remove('hide');
                    }
                    else
                    {
                        document.getElementById('declare-result').classList.remove('d-flex');
                        document.getElementById('declare-result').classList.add('hide');
                    }
                },
                error: function (response) {
                    console.log('Error:', response);
                }
            });
        });
    });

    $(document).on('change', '#status', function(){
        let id = this.value;

        $.ajax({
            url: "{{ url('changeStatus') }}",
            type: "GET",
            data: {id:id},
            success: function (response) {
                // window.location = "/events";
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
    })

    var inpuBet = document.querySelector("#bet-amount");
    var betButtons = document.querySelectorAll(".box-choices");
    var total = 0;

    betButtons.forEach(function(btn) {
        btn.addEventListener("click", function(e) {
            if(this.id == 'btn9'){
                inpuBet.value = "";
            }else{
                inpuBet.value = this.value;
            }
        });
    });

    var playerBet = document.querySelectorAll(".player-bet");

    playerBet.forEach(function(btn) {
        btn.addEventListener("click", function(e) {
        
        var betType = this.id;
        var betAmount = document.getElementById("bet-amount").value;
        var id = document.getElementById("fightId").value;
        var eventId = document.getElementById("eventId").value;
            
            if(betAmount >= 20){
                if(betType == "draw" && betAmount > 1000){
                    alert('MAX BET FOR DRAW IS 1,000');
                }else{
                    $.ajax({
                    url: "{{ url('checkFight') }}",
                    type: "GET",
                    data: {id:id},
                        success: function (response) {
                            if(response.isOpen == 1){
                                if (confirm("Are you sure you want to BET " + betAmount + " - " + betType.toUpperCase() + "?")) 
                                    {
                                        $.ajax({
                                            url: "{{ route('bet') }}",
                                            type: "GET",
                                            data: {amount:betAmount,bet_type:betType,id:id,eventId:eventId},
                                            success: function (response) {
                                                // window.location = "/events";
                                                if(!response.success){
                                                    alert('INVALID AMOUNT, TRY TO RECHARGE!');
                                                }else{
                                                    inpuBet.value = "";
                                                    $('#current_balance').html('$' + number_format(response[1][0].current_balance));
                                                    $('.meron-bet').html(number_format(response[2]));
                                                    $('#spanMeronReward').html(number_format(Math.ceil(response[2] * (response[5] / 100))));
                                                    $('.wala-bet').html(number_format(response[3]));
                                                    $('#spanWalaReward').html(number_format(Math.ceil(response[3] * (response[6] / 100))));
                                                    $('.draw-amount').html(number_format(response[4]));
                                                }
                                            },
                                            error: function (response) {
                                                console.log('Error:', response);
                                            }
                                        });
                                    }
                            }
                        },
                        error: function (response) {
                            console.log('Error:', response);
                        }
                    });
                }
                
            }else{
                alert('INVALID AMOUNT, TRY TO RECHARGE!');
            }
        });
    });


function number_format(number, decimals, dec_point, thousands_point) {

    if (number == null || !isFinite(number)) {
        throw new TypeError("number is not valid");
    }

    if (!decimals) {
        var len = number.toString().split('.').length;
        decimals = len > 1 ? len : 0;
    }

    if (!dec_point) {
        dec_point = '.';
    }

    if (!thousands_point) {
        thousands_point = ',';
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", dec_point);

    var splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);

    return number;
}

function checkReset(){
    var id = document.getElementById("fightId").value;
    var goNext = document.getElementById('go-next');

    $.ajax({
    url: "{{ url('checkResetAll') }}",
    type: "GET",
    data: {id:id},
        success: function (response) {
            if(response.checkUnpaidBet == 0){
                if(goNext != null){
                    goNext.classList.remove('hide');
                }
            }
        },
        error: function (response) {
            console.log('Error:', response);
        }
    });
}

})

</script>
<script src="{{ mix('js/app.js') }}"></script>

@endpush
@endsection