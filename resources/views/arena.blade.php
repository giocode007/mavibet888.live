@extends('layouts.player')
@section('content')
    <div id="main-arena">
        <div class="page-heading mx-2">
            @if (Auth::user()->role_type == 'Player')
            <h5 class="text-warning">{{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('dddd') }} 
                {{ $event[0]->event_name }} - {{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('MM/DD/Y') }}
            </h5>
            @else
            <h5 class="text-warning"><a href="{{ url('events/'.$event[0]->id) }}">
                {{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('dddd') }} {{ $event[0]->event_name }} - 
                {{ \Carbon\Carbon::parse($event[0]->fight_date_time)->isoFormat('MM/DD/Y') }}</a>
                <a href="javascript:void(0)" id="usersThatOnline"> ONLINE:<span id="onlineUsers"> 0 </span></a> 
            </h5>
            @endif
        </div>
        <div class="page-content">
            <section class="row">
                <div class="col-12 col-lg-6">

                    <div style="position: relative;overflow: hidden; padding-top: 56.25%;">
                        @if (Auth::user()->role_type == 'Player' && Auth::user()->current_balance >= 20)
                        <iframe
                        style="width: 100%; height: 100%; position: absolute;
                        top: 0;
                        left: 0;"
                        src="{{ $event[0]->video_code }}" 
                        frameborder="0" 
                        scrolling="no"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
                    @elseif(Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator')
                        <iframe
                        style="width: 100%; height: 100%; position: absolute;
                        top: 0;
                        left: 0;"
                        src="{{ $event[0]->video_code }}" 
                        frameborder="0" 
                        scrolling="no"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
                    @endif
                    </div>

                    


                    {{-- Operator --}}

                    <section>
                        @if (Auth::user()->role_type=='Operator' || Auth::user()->role_type == 'Declarator')
                        <div class="py-2">
                            <select class="form-control" id="status" name="status">
                                <option value="7">Select Status</option>
                                @foreach ($status as $key => $value)
                                    <option value="{{ $key }}" {{ ( $key == $selectedStatus[0]->id) ? 'selected' : '' }}> 
                                        {{ $value }} 
                                    </option>
                                @endforeach    
                            </select>
                        </div>

                        <div id="openclose" class="d-flex justify-content-around py-2 border">
                            <div class="align-self-sm-center">
                                <span class="text-white font-bold">BETTING STATUS : </span>
                            </div>
                            <div>
                                <button id="1" class="fight-status bg-primary p-2 text-white">OPEN</button>
                                <button id="0" class="fight-status bg-danger p-2 text-white">CLOSE</button>
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
                                <button id="nexting" class="hide bg-info p-2 font-bold"><span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span> LOADING...</button>
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
                                <button id="nexting" class="hide bg-info p-2 font-bold"><span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span> LOADING...</button>
                            </div>
                        </div>

                        <div id="refreshAllUsers" class="hide justify-content-around border py-2">
                            <div class="align-self-sm-center text-white font-bold">
                                REFRESH: 
                            </div>
                            <div>
                                <button id="refresh-users" class="bg-success p-2 text-white">REFRESH USERS</button>
                                <button id="refreshing" class="hide bg-success p-2 font-bold text-white"><span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span> REFRESHING...</button>
                            </div>
                        </div>

                        <div id="cancelFightdiv" class="hide py-2">
                            <select class="form-control" id="cancelFight" name="cancelFight">
                                <option value="{{ $fight[0]->id }}">Select Cancel Fight</option>
                                @foreach ($cancelFight as $key => $value)
                                    <option value="{{ $value }}" {{ ( $key == $selectedCancelFight->id) ? 'selected' : '' }}> 
                                        FIGHT # {{ $key }} 
                                    </option>
                                @endforeach    
                            </select>
                        </div>

                        <ul id="list-messages" class="px-1">
                        </ul>

                        @endif
                    </section>
                    
                </div>

                <div id="main" class="col-12 col-lg-6">
                    <div class="blink card bg-warning">
                        <div class="text-center p-1">
                            <span id="spanStatus" class="text-black font-bold text-uppercase">{{ $selectedStatus[0]->status_type }}</span>
                        </div>
                    </div>
                    <div class="wrapper">
                        <input id="fightId" type="hidden" value="{{ $fight[0]->id }}">
                        <input id="eventId" type="hidden" value="{{ $event[0]->id }}">
                        <input id="userId" type="hidden" value="{{ Auth::user()->id }}">
                        <div class="box betting">BETTING</div>
                        <div class="box result">LAST FIGHT #
                            @if(!empty($lastFight->result))
                                @if ($lastFight->result == 'meron')
                                <span id="spanLastResult" class="bg-danger p-1"> {{ $lastFight->fight_number }}  MERON </span>
                                @elseif ($lastFight->result == 'wala')
                                <span id="spanLastResult" class="bg-primary p-1"> {{ $lastFight->fight_number }} WALA </span>
                                @elseif ($lastFight->result == 'draw')
                                <span id="spanLastResult" class="bg-success p-1"> {{ $lastFight->fight_number }} WALA </span>
                                @elseif ($lastFight->result == 'cancel')
                                <span id="spanLastResult" class="bg-light-secondary p-1"> {{ $lastFight->fight_number }} CANCEL </span>
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
                            <div class="box-money meron-total-bet text-warning"> 
                                <span id="totalMeronBet" > @comma($allMeronBet)</span> 
                                @if (Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator')
                                    <span id="totalRealMeronBet" class="text-body"> ( @comma($allRealMeronBet) )</span>
                                @endif
                            </div>
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
                            <div class="box-money wala-total-bet text-warning"> 
                                <span id="totalWalaBet"> @comma($allWalaBet) </span>
                                @if (Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator')
                                    <span id="totalRealWalaBet" class="text-body"> ( @comma($allRealWalaBet) )</span>
                                @endif
                            </div>
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

                        <div class="box-draw-max draw-max-amount">DRAW WINS X 8. Max. DRAW bet 1000/fight</div>

                        <div class="mx-auto bg-danger result-meron box-result"><p id="result-meron" class="inner text-white">000</p></div>
                        <div class="mx-auto bg-primary result-wala box-result"><p id="result-wala" class="inner text-white">000</p></div>
                        <div class="mx-auto bg-success result-draw box-result"><p id="result-draw" class="inner text-white">000</p></div>
                        <div class="mx-auto bg-light-secondary result-cancel box-result"><p id="result-cancel" class="inner">000</p></div>
                        
                        <div class="mx-auto result-meron-text box-result-text"><p class="inner">MERON</p></div>
                        <div class="mx-auto result-wala-text box-result-text"><p class="inner">WALA</p></div>
                        <div class="mx-auto result-draw-text box-result-text"><p class="inner">DRAW</p></div>
                        <div class="mx-auto result-cancel-text box-result-text "><p class="inner ">CANCELLED</p></div>

                        <div class="user_field_group" id="container_data" style="overflow-y: hidden; 
                        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.25); border-radius: 0px 0px 5px 5px;">
                            <div id="display_trade_group" class="cards_result"></div>
                        </div>
                        



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

@if (Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator')
<div class="modal fade w-100" id="ajax-crud-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="eventModal"></h4>
        </div>
        <div class="modal-body">
            <div  id="avatars">
                
            </div>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
    </div>
</div>
@endif

@push('scripts')

<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    checkResult();
    checkBet();

    $('body').on('click', '#refresh-users', function () {
        var id = document.getElementById("fightId").value;
        var eventId = document.getElementById("eventId").value;

        document.getElementById('refresh-users').classList.add('hide');
        document.getElementById('refreshing').classList.remove('hide');

        $.ajax({
        url: "{{ url('refreshUsers') }}",
        type: "GET",
        data: {id:id,eventId:eventId},  
            success: function (response) {
                
            },
            error: function (response) {
                console.log('Error:', response);
                $('#refreshing').html('ERROR');
            }
        });
    })


    $('body').on('click', '#reset-all', function () {
        
        var id = document.getElementById("fightId").value;
        var eventId = document.getElementById("eventId").value;

        document.getElementById('reset-all').classList.add('hide');
        document.getElementById('resetting').classList.remove('hide');

        document.getElementById('declare-result').classList.remove('d-flex');
        document.getElementById('declare-result').classList.add('hide');
        document.getElementById('openclose').classList.remove('d-flex');
        document.getElementById('openclose').classList.add('hide');

        $.ajax({
        url: "{{ url('checkFight') }}",
        type: "GET",
        data: {id:id},
            success: function (response) {
                if(response.result != null){
                    $.ajax({
                    url: "{{ url('resetAll') }}",
                    type: "GET",
                    data: {id:id,eventId:eventId},
                        success: function (response) {
                            document.getElementById('reset-all').classList.remove('hide');
                            document.getElementById('resetting').classList.add('hide');
                            document.getElementById('go-next').classList.remove('hide');
                            document.getElementById('refreshAllUsers').classList.remove('hide');
                            document.getElementById('refreshAllUsers').classList.add('d-flex');

                            if(response[0]['data'].length != 0){
                                document.getElementById('cancelFightdiv').classList.remove('hide');

                                var len = response[0]['data'].length;

                                $('#cancelFight').find('option').not(':first').remove();

                                if(len > 0){
                                // Read data and create <option >
                                    for(var i=0; i<len; i++){

                                        var id = response[0]['data'][i].id;
                                        var name = response[0]['data'][i].fight_number;

                                        var option = "<option value='"+id+"'>FIGHT # "+name+"</option>"; 

                                        $("#cancelFight").append(option); 
                                    }
                                }
                                
                            }else{
                                document.getElementById('cancelFightdiv').classList.add('hide');
                            }
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
        var eventId = document.getElementById("eventId").value;
        var actions = 'goNext';

        document.getElementById('go-next').classList.add('hide');
        document.getElementById('nexting').classList.remove('hide');

        $.ajax({
        url: "{{ url('goNext') }}",
        type: "GET",
        data: {id:id,eventId:eventId,actions:actions},  
            success: function (response) {

                document.getElementById('openclose').classList.add('d-flex');
                document.getElementById('openclose').classList.remove('hide');

                document.getElementById('go-next').classList.remove('hide');
                document.getElementById('nexting').classList.add('hide');

                document.getElementById('nextFight').classList.remove('d-flex');
                document.getElementById('nextFight').classList.add('hide');
                
                document.getElementById('go-next').classList.add('hide');

                document.getElementById('refreshAllUsers').classList.add('hide');
                document.getElementById('refreshAllUsers').classList.remove('d-flex');

                document.getElementById('cancelFightdiv').classList.add('hide');

                meronResult = 0;
                walaResult = 0;
                drawResult= 0;
                cancelResult = 0;
                result = [];
                response.reduce(function (r, a) {
                    if (a.result !== r) {
                        result.push([]);
                    }
                    result[result.length - 1].push(a);
                    return a.result;
                }, undefined);

                var d = JSON.stringify(result, 0, 4);
                var jsonParse = JSON.parse(d);
                var html = '';
                html += '<tr>';
                jsonParse.forEach(function(el){
                    html += '<td>';
                        if(el[0].result == 'meron'){
                            html += '<p style="background: #EEEEEE; color: #ED5659; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">MERON</p>';
                        }else if(el[0].result == 'wala'){
                            html += '<p style="background: #EEEEEE; color: #1072BA; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">WALA</p>';
                        }else if(el[0].result == 'draw'){
                            html += '<p style="background: #EEEEEE; color: #198754; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">DRAW</p>';
                        }else if(el[0].result == 'cancel'){
                            html += '<p style="background: #EEEEEE; color: #999999; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">CANCEL</p>';
                        }

                        el.forEach(function(ele){
                            if(ele.result == 'meron'){
                                meronResult++;
                                html += '<button type="button" class="mx-3 btn trend_output btn_result" style="background: #ED5659;">'+ele.fight_number+'</button><br>';
                            }else if(ele.result == 'wala'){
                                walaResult++;
                                html += '<button type="button" class="mx-2 btn trend_output btn_result" style="background: #1072BA;">'+ele.fight_number+'</button><br>';
                            }else if(ele.result == 'draw'){
                                drawResult++;
                                html += '<button type="button" class="mx-2 btn trend_output btn_result" style="background: #198754;">'+ele.fight_number+'</button><br>';
                            }else if(ele.result == 'cancel'){
                                cancelResult++;
                                html += '<button type="button" class="mx-3 btn trend_output btn_result" style="background: #999999;">'+ele.fight_number+'</button><br>';
                            }
                        })
                        html += '</td>';
                })

                $('#result-meron').html(meronResult);
                $('#result-wala').html(walaResult);
                $('#result-draw').html(drawResult);
                $('#result-cancel').html(cancelResult);

                html += '</tr>';
                $('#display_trade_group').html(html);

                var listElements = document.querySelectorAll("#list-messages li");

                for (var i = 0; (li = listElements[i]); i++) {
                li.parentNode.removeChild(li);
                }

                
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
    })

    $('body').on('click', '#usersThatOnline', function(){
        $('#ajax-crud-modal').modal('show');
    })

    $('body').on('change', '#cancelFight', function () {
        var id = document.getElementById("fightId").value;
        var eventId = document.getElementById("eventId").value;
        var actions = 'cancelFight';
        let cancelFight = this.value;

        $.ajax({
        url: "{{ url('goNext') }}",
        type: "GET",
        data: {id:id,eventId:eventId,actions:actions,cancelFight:cancelFight},  
            success: function (response) {
                if(response[0]['data'] != null){

                var len = response[0]['data'].length;

                $('#cancelFight').find('option').not(':first').remove();

                if(len > 0){
                // Read data and create <option >
                    for(var i=0; i<len; i++){

                        var id = response[0]['data'][i].id;
                        var name = response[0]['data'][i].fight_number;

                        var option = "<option value='"+id+"'>FIGHT # "+name+"</option>"; 

                        $("#cancelFight").append(option); 
                    }
                }
                    
                }else{
                    document.getElementById('cancelFightdiv').classList.add('hide');
                }

                document.getElementById('nextFight').classList.remove('d-flex');
                document.getElementById('nextFight').classList.add('hide');
                document.getElementById('go-next').classList.add('hide');
                
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
    })

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


    var resultButton = document.querySelectorAll(".buttonResult");
    resultButton.forEach(function(btn) {
        btn.addEventListener("click", function(e) {
        
        var result = this.value;
        var payoutMeron = removeComma(document.getElementById("meronPayout").textContent ) / 100;
        var payoutWala = removeComma(document.getElementById("walaPayout").textContent) / 100;
        var id = document.getElementById("fightId").value;
        var eventId = document.getElementById("eventId").value;


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
                                    data: {id:id,eventId:eventId,result:result,payoutMeron:payoutMeron,payoutWala:payoutWala},
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
            var id = document.getElementById("fightId").value;
            
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
                alert('INVALID AMOUNT, MINIMUM BET IS 20!');
            }
        });
    });

function addBet(name, message, color="white"){
    listMessage = document.getElementById('list-messages');

    const li = document.createElement('li');
        
    li.classList.add('d-flex', 'flex-col');

    const span = document.createElement('span')
    span.classList.add('message-author');
    span.classList.add('text-warning');
    span.textContent = name + ' /';

    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;

    messageSpan.style.color = color;

    li.append(span, messageSpan);

    listMessage.append(li);
}

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

function removeComma(amount) {
    if (amount != null) {
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
    }
    return parseInt(amount);
}

function checkResult(){
    var eventId = document.getElementById("eventId").value;

    $.ajax({
    url: "{{ url('checkResult') }}",
    type: "GET",
    data: {eventId:eventId},
        success: function (response) {
            
            meronResult = 0;
            walaResult = 0;
            drawResult= 0;
            cancelResult = 0;
            result = [];
            response.reduce(function (r, a) {
                if (a.result !== r) {
                    result.push([]);
                }
                result[result.length - 1].push(a);
                return a.result;
            }, undefined);

            var d = JSON.stringify(result, 0, 4);

            var jsonParse = JSON.parse(d);
            var html = '';
            html += '<tr>';
            jsonParse.forEach(function(el){
                html += '<td>';
                    if(el[0].result == 'meron'){
                        html += '<p style="background: #EEEEEE; color: #ED5659; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">MERON</p>';
                    }else if(el[0].result == 'wala'){
                        html += '<p style="background: #EEEEEE; color: #1072BA; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">WALA</p>';
                    }else if(el[0].result == 'draw'){
                        html += '<p style="background: #EEEEEE; color: #198754; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">DRAW</p>';
                    }else if(el[0].result == 'cancel'){
                        html += '<p style="background: #EEEEEE; color: #999999; padding: 3px 0 0 8px; font-weight: 700; height: 30px;">CANCEL</p>';
                    }

                    el.forEach(function(ele){
                        if(ele.result == 'meron'){
                            meronResult++;
                            html += '<button type="button" class="mx-3 btn trend_output btn_result" style="background: #ED5659;">'+ele.fight_number+'</button><br>';
                        }else if(ele.result == 'wala'){
                            walaResult++;
                            html += '<button type="button" class="mx-2 btn trend_output btn_result" style="background: #1072BA;">'+ele.fight_number+'</button><br>';
                        }else if(ele.result == 'draw'){
                            drawResult++;
                            html += '<button type="button" class="mx-2 btn trend_output btn_result" style="background: #198754;">'+ele.fight_number+'</button><br>';
                        }else if(ele.result == 'cancel'){
                            cancelResult++;
                            html += '<button type="button" class="mx-3 btn trend_output btn_result" style="background: #999999;">'+ele.fight_number+'</button><br>';
                        }
                    })
                    html += '</td>';
            })

            $('#result-meron').html(meronResult);
            $('#result-wala').html(walaResult);
            $('#result-draw').html(drawResult);
            $('#result-cancel').html(cancelResult);

            html += '</tr>';

            $('#display_trade_group').html(html);

           
        },
        error: function (response) {
            console.log('Error:', response);
        }
    });
}

function checkBet(){
    var fightId = document.getElementById("fightId").value;

    $.ajax({
    url: "{{ url('checkBet') }}",
    type: "GET",
    data: {fightId:fightId},
        success: function (response) {
            if(response != null){
                response.forEach(element => {
                addBet(element.user_name, ' Bet on ' + element.bet_type + ' = ' + element.amount);
            });
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