<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Fight;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;


class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isOpDec']);
    }

    public function index()
    {
        $data['events'] = Event::orderBy('id', 'desc')->get();
        return view('operators.events.index',$data);
    }

    public function store(Request $request)
    {
        $event = Event::updateOrCreate([
            'id' => $request->event_id
        ],
        [
            'event_name'     => $request->event_name,
            'fight_date_time' => $request->fight_date_time,
            'location' => $request->location,
            'video_code' => $request->video_code,
            'palasada' => $request->palasada,
            'user_id' => Auth::user()->id,
            'status' => $request->status,
        ]);  
        
        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();
        $description  = 'event created/updated';
    
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);

        return response()->json($event);
    }

    public function show($id)
    {
        $fights = DB::table('fights')->where('event_id', $id)->orderBy('id', 'desc')->get();
        $selectedEvent = DB::table('events')->where('id', $id)->get();
        
        return view('operators.events.show', compact('fights','selectedEvent'));
    }

    public function getFight(Request $request)
    {
        $fightId = $request->fightId;

        $fight = DB::table('fights')->where('id', $fightId)->get();

        return response()->json($fight);
    }

    public function reverseFight(Request $request)
    {
        $fightId = $request->fightId;
        $result = $request->result;

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        $fight = DB::table('fights')->select('id', 'event_id','payoutMeron','payoutWala','result', 'fight_number')->where('id', $fightId)->get();
        
        $event = DB::table('events')->select('event_name')->where('id', $fight[0]->event_id)->first();

        $allBets = DB::table('bettings')->select('id','amount', 'result','user_id', 'bet_type')->where('fight_id', $fightId)->where('role_type', 'Player')->where('status', 1)->get();

        $meronPayout = $fight[0]->payoutMeron;
        $walaPayout = $fight[0]->payoutWala;
        $lastResult = $fight[0]->result;


        if($result != $lastResult){
            foreach($allBets as $allBet){
                $totalBalance = 0;
                $reverseWinBet = 0; 

                
                //Retrieve bet amount
                if($lastResult == 'cancel' || $lastResult == 'draw'){
                    $t = 0;
                    $r = 0;

                    $getCancelPayment = DB::table('users')->select('id', 'current_balance', 'user_name')->where('id', $allBet->user_id)->get();

                    if($allBet->bet_type == 'draw' && $lastResult == 'draw'){
                        if($allBet->amount >= 1000){
                            $extra = $allBet->amount - 1000;
                            $r = 8000 + $extra;
                        }else{
                            $r = $allBet->amount * 8;
                        }

                        $t = $getCancelPayment[0]->current_balance - $r;

                    }else{

                        $r = $allBet->amount;
                        $t = $getCancelPayment[0]->current_balance - $r;
                    }

                    DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $t ]);


                    $currBalance = DB::table('users')->select('id','current_balance')->where('id', $allBet->user_id)->first();

                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'transaction_type' => 'reverse',
                        'amount' => 0,
                        'current_balance' => $currBalance->current_balance,
                        'status' => 4,
                        'note'     => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                        'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                        'to' => $getCancelPayment[0]->user_name,
                        'approved_date_time' => $todayDate,
                        'request_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
                    ]);

                    DB::table('activity_logs')->where('user_id', $currBalance->id)
                    ->where('betting_id', $allBet->id)
                    ->where('status', 1)
                    ->update([
                        'status' => 0,
                    ]);

                    $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - '. $event->event_name . ', ' . $allBet->amount . ' points retrieved';
                    $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you lose ' . $allBet->amount;

                    $activityLog = [
                        'user_id'        => $allBet->user_id,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];
            
                    DB::table('activity_logs')->insert($activityLog);

                    $activityLog1 = [
                        'user_id'        => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'status' => 1,
                        'description' => $description1,
                        'date_time'   => $todayDate,
                    ];
            
                    DB::table('activity_logs')->insert($activityLog1);
                }

                $user = DB::table('users')->select('id', 'current_balance', 'agent_code', 'user_name', 'role_type')->where('id', $allBet->user_id)->get();

                //Commission
                if($user[0]->role_type == 'Player'){
                    if(($result != 'cancel' && $result != 'draw') 
                        && ($lastResult != 'meron' && $lastResult != 'wala')){
                        if($allBet->bet_type == 'meron' || $allBet->bet_type == 'wala'){
    
                            $checkAgent = DB::table('users')->select('id','user_name','current_commission',  'current_balance' , 
                            'commission_percent', 'player_code', 'agent_code', 'role_type')
                            ->where('player_code', $user[0]->agent_code)->get();
            
                            if($checkAgent[0]->role_type == 'Sub_Operator'){
                                $commission = $allBet->amount * $checkAgent[0]->commission_percent;
                                $currCommission = $checkAgent[0]->current_commission + $commission;
                
                                DB::table('users')->where('id', $checkAgent[0]->id)
                                ->update([
                                    'current_commission' => $currCommission,
                                ]);

                                $transaction = Transactions::updateOrCreate([
                                    'id' => $request->transaction_id
                                ],
                                [
                                    'user_id' => $checkAgent[0]->id,
                                    'betting_id' => $allBet->id,
                                    'transaction_type' => 'commission',
                                    'amount' => $commission,
                                    'current_balance' => $checkAgent[0]->current_balance,
                                    'current_commission' => $currCommission,
                                    'status' => 1,
                                    'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                    'from' => $user[0]->user_name,
                                    'to' => $checkAgent[0]->user_name,
                                    'approved_date_time' => $todayDate,
                                    'approve_by' => Auth::user()->user_name,
                                ]);   

                                DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                ->where('betting_id', $allBet->id)
                                ->where('status', 1)
                                ->update([
                                    'status' => 0,
                                ]);
                
                                $description  = 'Reverse: Received ' . $commission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                    
                                $activityLog = [
                                    'user_id' => $checkAgent[0]->id,
                                    'betting_id' => $allBet->id,
                                    'status' => 1,
                                    'description' => $description,
                                    'date_time'   => $todayDate,
                                ];
                
                                DB::table('activity_logs')->insert($activityLog);
                            }else{
                                if($checkAgent[0]->role_type == 'Master_Agent'){
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent',  'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
            
                                    $subOpCommission = $allBet->amount * $checkSubOp[0]->commission_percent;
                                    $masterCommission = $allBet->amount * $checkAgent[0]->commission_percent;
                
                                    $totalSubOpCommission = $subOpCommission - $masterCommission;
                                    $totalSubOpCommission1 = $totalSubOpCommission + $checkSubOp[0]->current_commission;
                                    $totalMasterCommission = $masterCommission + $checkAgent[0]->current_commission;
                
                                    DB::table('users')->where('id', $checkAgent[0]->id)
                                    ->update([
                                        'current_commission' => $totalMasterCommission,
                                    ]);
                
                                    DB::table('users')->where('id', $checkSubOp[0]->id)
                                    ->update([
                                        'current_commission' => $totalSubOpCommission1,
                                    ]);
                    
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $masterCommission,
                                        'current_balance' => $checkAgent[0]->current_balance,
                                        'current_commission' => $totalMasterCommission,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $user[0]->user_name,
                                        'to' => $checkAgent[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalSubOpCommission,
                                        'current_balance' => $checkSubOp[0]->current_balance,
                                        'current_commission' => $totalSubOpCommission1,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkAgent[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => $checkAgent[0]->user_name,
                                    ]);   

                                    DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);
                    
                                    $description  = 'Reverse: Received ' . $masterCommission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                                    $description1 = 'Reverse: Received ' . $totalSubOpCommission . ' Commission from ' . $checkAgent[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                        
                                    $activityLog = [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
    
                                    DB::table('activity_logs')->insert($activityLog1);
                                }else{
                                    $checkMaster = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent', 'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent', 'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkMaster[0]->agent_code)->get();
            
                                    $totalGoldCommission = $allBet->amount * $checkAgent[0]->commission_percent;
                                    $masterCommission = $allBet->amount * $checkMaster[0]->commission_percent;
                                    $subOpCommission = $allBet->amount * $checkSubOp[0]->commission_percent;
                
                                    
                                    $totalMasterCommission = $masterCommission - $totalGoldCommission;
                                    $totalSubOpCommission = $subOpCommission - $masterCommission;
            
                                    $commissionGold = $totalGoldCommission + $checkAgent[0]->current_commission;
                                    $commissionMaster = $totalMasterCommission + $checkMaster[0]->current_commission;
                                    $commissionSubOp = $totalSubOpCommission + $checkSubOp[0]->current_commission;
                
                                    DB::table('users')->where('id', $checkAgent[0]->id)
                                    ->update([
                                        'current_commission' => $commissionGold,
                                    ]);
                
                                    DB::table('users')->where('id', $checkMaster[0]->id)
                                    ->update([
                                        'current_commission' => $commissionMaster,
                                    ]);
            
                                    DB::table('users')->where('id', $checkSubOp[0]->id)
                                    ->update([
                                        'current_commission' => $commissionSubOp,
                                    ]);
                    
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalGoldCommission,
                                        'current_balance' => $checkAgent[0]->current_balance,
                                        'current_commission' => $commissionGold,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $user[0]->user_name,
                                        'to' => $checkAgent[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
            
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkMaster[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalMasterCommission,
                                        'current_balance' => $checkMaster[0]->current_balance,
                                        'current_commission' => $commissionMaster,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkAgent[0]->user_name,
                                        'to' => $checkMaster[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                    
            
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalSubOpCommission,
                                        'current_balance' => $checkSubOp[0]->current_balance,
                                        'current_commission' => $commissionSubOp,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkMaster[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => $checkAgent[0]->user_name,
                                    ]);   
                    
                                    DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkMaster[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    $description  = 'Reverse: Received ' . $totalGoldCommission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                                    $description1 = 'Reverse: Received ' . $totalMasterCommission . ' Commission from ' . $checkAgent[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                                    $description2 = 'Reverse: Received ' . $totalSubOpCommission . ' Commission from ' . $checkMaster[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event->event_name;
                        
                                    $activityLog = [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id' => $checkMaster[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);
            
                                    $activityLog2 = [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description2,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog2);
                                }
                            }
                        }
                    }else if(($result == 'cancel' || $result == 'draw') 
                    && ($lastResult == 'meron' || $lastResult == 'wala')){
                        if($allBet->bet_type == 'meron' || $allBet->bet_type == 'wala'){
    
                            $checkAgent = DB::table('users')->select('id','user_name','current_commission', 'current_balance' ,
                            'commission_percent', 'player_code', 'agent_code', 'role_type')
                            ->where('player_code', $user[0]->agent_code)->get();
            
                            if($checkAgent[0]->role_type == 'Sub_Operator'){
                                $commission = $allBet->amount * $checkAgent[0]->commission_percent;

                                $currCommission = abs($checkAgent[0]->current_commission - $commission);
                
                                DB::table('users')->where('id', $checkAgent[0]->id)
                                ->update([
                                    'current_commission' => $currCommission,
                                ]);

                                DB::table('transactions')
                                ->where('user_id', $checkAgent[0]->id)
                                ->where('betting_id', $allBet->id)
                                ->where('status', '!=', 3)
                                ->update([
                                    'status' => 2, 
                                ]);
                
                                $transaction = Transactions::updateOrCreate([
                                    'id' => $request->transaction_id
                                ],
                                [
                                    'user_id' => $checkAgent[0]->id,
                                    'betting_id' => $allBet->id,
                                    'transaction_type' => 'commission',
                                    'amount' => $commission,
                                    'current_balance' => $checkAgent[0]->current_balance,
                                    'current_commission' => $currCommission,
                                    'status' => 2,
                                    'note' => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                    'from' => $user[0]->user_name,
                                    'to' => $checkAgent[0]->user_name,
                                    'approved_date_time' => $todayDate,
                                    'approve_by' => Auth::user()->user_name,
                                ]);   

                                DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                ->where('betting_id', $allBet->id)
                                ->where('status', 1)
                                ->update([
                                    'status' => 0,
                                ]);
                
                                $description  = 'Reverse: retrieved ' . $commission . ' Commission from ' . $user[0]->user_name . ' ' . $event->event_name;
                    
                                $activityLog = [
                                    'user_id' => $checkAgent[0]->id,
                                    'description' => $description,
                                    'date_time'   => $todayDate,
                                ];
                
                                DB::table('activity_logs')->insert($activityLog);

                                

                            }else{
                                if($checkAgent[0]->role_type == 'Master_Agent'){
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent', 'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
            
                                    $subOpCommission = $allBet->amount * $checkSubOp[0]->commission_percent;
                                    $masterCommission = $allBet->amount * $checkAgent[0]->commission_percent;
                
                                    $totalSubOpCommission = $subOpCommission - $masterCommission;

                                    $totalSubOpCommission1 = abs($totalSubOpCommission - $checkSubOp[0]->current_commission);
                                    $totalMasterCommission = abs($masterCommission - $checkAgent[0]->current_commission);
                
                                    DB::table('users')->where('id', $checkAgent[0]->id)
                                    ->update([
                                        'current_commission' => $totalMasterCommission,
                                    ]);
                
                                    DB::table('users')->where('id', $checkSubOp[0]->id)
                                    ->update([
                                        'current_commission' => $totalSubOpCommission1,
                                    ]);

                                    DB::table('transactions')
                                    ->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', '!=', 3)
                                    ->update([
                                        'status' => 2, 
                                    ]);

                                    DB::table('transactions')
                                    ->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', '!=', 3)
                                    ->update([
                                        'status' => 2, 
                                    ]);
                    
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $masterCommission,
                                        'current_balance' => $checkAgent[0]->current_balance,
                                        'current_commission' => $totalMasterCommission,
                                        'status' => 2,
                                        'note'     => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $user[0]->user_name,
                                        'to' => $checkAgent[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalSubOpCommission,
                                        'current_balance' => $checkSubOp[0]->current_balance,
                                        'current_commission' => $totalSubOpCommission1,
                                        'status' => 2,
                                        'note'     => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkAgent[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => $checkAgent[0]->user_name,
                                    ]);   
                    
                                    DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    $description  = 'Reverse: retrieved ' . $masterCommission . ' Commission from ' . $user[0]->user_name . ' ' . $event->event_name;
                                    $description1 = 'Reverse: retrieved ' . $totalSubOpCommission . ' Commission from ' . $checkAgent[0]->user_name . ' ' . $event->event_name;
                        
                                    $activityLog = [
                                        'user_id' => $checkAgent[0]->id,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id' => $checkSubOp[0]->id,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);

                                }else{
                                    $checkMaster = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent', 'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission', 'commission_percent', 'current_balance' ,
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkMaster[0]->agent_code)->get();
            
                                    $totalGoldCommission = $allBet->amount * $checkAgent[0]->commission_percent;
                                    $masterCommission = $allBet->amount * $checkMaster[0]->commission_percent;
                                    $subOpCommission = $allBet->amount * $checkSubOp[0]->commission_percent;
                
                                    
                                    $totalMasterCommission = $masterCommission - $totalGoldCommission;
                                    $totalSubOpCommission = $subOpCommission - $masterCommission;
            
                                    $commissionGold = abs($totalGoldCommission - $checkAgent[0]->current_commission);
                                    $commissionMaster = abs($totalMasterCommission - $checkMaster[0]->current_commission);
                                    $commissionSubOp = abs($totalSubOpCommission - $checkSubOp[0]->current_commission);
                
                                    DB::table('users')->where('id', $checkAgent[0]->id)
                                    ->update([
                                        'current_commission' => $commissionGold,
                                    ]);
                
                                    DB::table('users')->where('id', $checkMaster[0]->id)
                                    ->update([
                                        'current_commission' => $commissionMaster,
                                    ]);
            
                                    DB::table('users')->where('id', $checkSubOp[0]->id)
                                    ->update([
                                        'current_commission' => $commissionSubOp,
                                    ]);

                                    DB::table('transactions')
                                    ->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', '!=', 3)
                                    ->update([
                                        'status' => 2, 
                                    ]);

                                    DB::table('transactions')
                                    ->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', '!=', 3)
                                    ->update([
                                        'status' => 2, 
                                    ]);

                                    DB::table('transactions')
                                    ->where('user_id', $checkMaster[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', '!=', 3)
                                    ->update([
                                        'status' => 2, 
                                    ]);
                    
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalGoldCommission,
                                        'current_balance' => $checkAgent[0]->current_balance,
                                        'current_commission' => $commissionGold,
                                        'status' => 2,
                                        'note'     => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $user[0]->user_name,
                                        'to' => $checkAgent[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
            
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkMaster[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalMasterCommission,
                                        'current_balance' => $checkMaster[0]->current_balance,
                                        'current_commission' => $commissionMaster,
                                        'status' => 2,
                                        'note'     => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkAgent[0]->user_name,
                                        'to' => $checkMaster[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                    
            
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalSubOpCommission,
                                        'current_balance' => $checkSubOp[0]->current_balance,
                                        'current_commission' => $commissionSubOp,
                                        'status' => 2,
                                        'note'     => 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                        'from' => $checkMaster[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => $checkAgent[0]->user_name,
                                    ]);   

                                    DB::table('activity_logs')->where('user_id', $checkAgent[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkMaster[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);

                                    DB::table('activity_logs')->where('user_id', $checkSubOp[0]->id)
                                    ->where('betting_id', $allBet->id)
                                    ->where('status', 1)
                                    ->update([
                                        'status' => 0,
                                    ]);
                    
                                    $description  = 'Reverse: retrieved ' . $totalGoldCommission . ' Commission from ' . $user[0]->user_name . ' ' . $event->event_name;
                                    $description1 = 'Reverse: retrieved ' . $totalMasterCommission . ' Commission from ' . $checkAgent[0]->user_name . ' ' . $event->event_name;
                                    $description2 = 'Reverse: retrieved ' . $totalSubOpCommission . ' Commission from ' . $checkMaster[0]->user_name . ' ' . $event->event_name;
                        
                                    $activityLog = [
                                        'user_id' => $checkAgent[0]->id,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id' => $checkMaster[0]->id,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);
            
                                    $activityLog2 = [
                                        'user_id' => $checkSubOp[0]->id,
                                        'description' => $description2,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog2);
                                }
                            }
                        }
                    }
                }
                

                if($lastResult == 'cancel'){

                    if($result == $allBet->bet_type){
                        if($result == 'meron'){
                            $reverseWinBet = $allBet->amount * $meronPayout;
                        }
                        elseif($result == 'wala'){
                            $reverseWinBet = $allBet->amount * $walaPayout;
                        }
                        else{
                            if($allBet->amount >= 1000){
                                $extra = $allBet->amount - 1000;
                                $reverseWinBet = 8000 + $extra;
                            }else{
                                $reverseWinBet = $allBet->amount * 8;
                            }
                        }

                        $totalBalance = $user[0]->current_balance + round($reverseWinBet);

                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('transactions')
                        ->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('transaction_type', 'result')
                        ->update([
                            'status' => 3, 
                        ]);
                        
                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => round($reverseWinBet),
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'WIN: result: ' . $result . ' = Bet: ' . $allBet->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'reverse',
                            'amount' => 0,
                            'current_balance' => $totalBalance,
                            'status' => 3,
                            'note'     => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        DB::table('activity_logs')->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);
                        
                        $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you won ' . round($reverseWinBet);
                        $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you won ' . round($reverseWinBet);
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog);

                        $activityLog1 = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description1,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog1);
                    }
                    
                } 
                
                if($result == 'cancel' || $result == 'draw'){
                    if($lastResult == $allBet->bet_type && $lastResult != 'draw'){
                        if($lastResult == 'meron'){
                            $reverseWinBet = $allBet->amount * $meronPayout;
                        }
                        elseif($lastResult == 'wala'){
                            $reverseWinBet = $allBet->amount * $walaPayout;
                        }

                        $totalBalance = ($user[0]->current_balance - round($reverseWinBet)) + $allBet->amount;
                        
                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('transactions')
                        ->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('transaction_type', 'result')
                        ->update([
                            'status' => 3, 
                        ]);

                        if($result == 'cancel'){
                            $transaction = Transactions::updateOrCreate([
                                'id' => $request->transaction_id
                            ],
                            [
                                'user_id' => $allBet->user_id,
                                'betting_id' => $allBet->id,
                                'transaction_type' => 'result',
                                'amount' => $allBet->amount,
                                'current_balance' => $totalBalance,
                                'status' => 1,
                                'note'     => 'CANCEL: ' . $allBet->amount . ' points returned',
                                'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                'to' => $user[0]->user_name,
                                'approved_date_time' => $todayDate,
                                'request_date_time' => $todayDate,
                                'approve_by' => Auth::user()->user_name,
                            ]); 
                        }else{
                            $transaction = Transactions::updateOrCreate([
                                'id' => $request->transaction_id
                            ],
                            [
                                'user_id' => $allBet->user_id,
                                'betting_id' => $allBet->id,
                                'transaction_type' => 'result',
                                'amount' => $allBet->amount,
                                'current_balance' => $totalBalance,
                                'status' => 1,
                                'note'     => 'DRAW: ' . $allBet->amount . ' points returned',
                                'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                'to' => $user[0]->user_name,
                                'approved_date_time' => $todayDate,
                                'request_date_time' => $todayDate,
                                'approve_by' => Auth::user()->user_name,
                            ]); 
                        }
                        

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'reverse',
                            'amount' => 0,
                            'current_balance' => $totalBalance,
                            'status' => 3,
                            'note'     => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        DB::table('activity_logs')->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);

                        $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' result cancel, ' . $allBet->amount . ' points returned';
                        $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' result cancel, ' . $allBet->amount . ' points returned';
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog);

                        $activityLog1 = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description1,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog1);

                    }elseif($result == 'draw' && $allBet->bet_type == 'draw'){
                        //nothing
                    }else{

                        $reverseWinBet = $allBet->amount;

                        $totalBalance = $user[0]->current_balance + round($reverseWinBet);

                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('transactions')
                        ->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('transaction_type', 'result')
                        ->update([
                            'status' => 3, 
                        ]);

                        if($result == 'cancel'){
                            $transaction = Transactions::updateOrCreate([
                                'id' => $request->transaction_id
                            ],
                            [
                                'user_id' => $allBet->user_id,
                                'betting_id' => $allBet->id,
                                'transaction_type' => 'result',
                                'amount' => round($reverseWinBet),
                                'current_balance' => $totalBalance,
                                'status' => 1,
                                'note'     => 'CANCEL: ' . $allBet->amount . ' points returned',
                                'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                'to' => $user[0]->user_name,
                                'approved_date_time' => $todayDate,
                                'request_date_time' => $todayDate,
                                'approve_by' => Auth::user()->user_name,
                            ]); 
                        }else{
                            $transaction = Transactions::updateOrCreate([
                                'id' => $request->transaction_id
                            ],
                            [
                                'user_id' => $allBet->user_id,
                                'betting_id' => $allBet->id,
                                'transaction_type' => 'result',
                                'amount' => round($reverseWinBet),
                                'current_balance' => $totalBalance,
                                'status' => 1,
                                'note'     => 'DRAW: ' . $allBet->amount . ' points returned',
                                'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                                'to' => $user[0]->user_name,
                                'approved_date_time' => $todayDate,
                                'request_date_time' => $todayDate,
                                'approve_by' => Auth::user()->user_name,
                            ]); 
                        }

                        

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'reverse',
                            'amount' => 0,
                            'current_balance' => $totalBalance,
                            'status' => 3,
                            'note'     => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        DB::table('activity_logs')->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);

                        $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' result cancel, ' . $allBet->amount . ' points returned';
                        $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' result cancel, ' . $allBet->amount . ' points returned';

                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog);

                        $activityLog1 = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description1,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog1);

                    }
                }else if($lastResult == $allBet->bet_type){

                    if($lastResult == 'meron'){
                        $reverseWinBet = $allBet->amount * $meronPayout;
                    }
                    elseif($lastResult == 'wala'){
                        $reverseWinBet = $allBet->amount * $walaPayout;
                    }
                    else{
                        if($allBet->amount >= 1000){
                            $extra = $allBet->amount - 1000;
                            $reverseWinBet = 8000 + $extra;
                        }else{
                            $reverseWinBet = $allBet->amount * 8;
                        }
                    }
        
                    $totalBalance = $user[0]->current_balance - round($reverseWinBet);

                    DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                    DB::table('transactions')
                    ->where('user_id', $allBet->user_id)
                    ->where('betting_id', $allBet->id)
                    ->where('transaction_type', 'result')
                    ->update([
                        'status' => 3, 
                    ]);

                    $currBalance = DB::table('users')->select('id','current_balance')->where('id', $allBet->user_id)->first();

                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'transaction_type' => 'result',
                        'amount' => 0,
                        'current_balance' => $currBalance->current_balance,
                        'status' => 2,
                        'note'     => 'LOSE: result: ' . $result . ' = Bet: ' . $allBet->bet_type,
                        'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                        'to' => $user[0]->user_name,
                        'approved_date_time' => $todayDate,
                        'request_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
                    ]); 

                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'transaction_type' => 'reverse',
                        'amount' => 0,
                        'current_balance' => $totalBalance,
                        'status' => 4,
                        'note' => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                        'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                        'to' => $user[0]->user_name,
                        'approved_date_time' => $todayDate,
                        'request_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
                    ]); 

                    DB::table('activity_logs')->where('user_id', $allBet->user_id)
                    ->where('betting_id', $allBet->id)
                    ->where('status', 1)
                    ->update([
                        'status' => 0,
                    ]);

                    $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you lose ' . $allBet->amount;
                    $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you lose ' . $allBet->amount;
                
                    $activityLog = [
                        'user_id'        => $allBet->user_id,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];
            
                    DB::table('activity_logs')->insert($activityLog);

                    $activityLog1 = [
                        'user_id'        => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'status' => 1,
                        'description' => $description1,
                        'date_time'   => $todayDate,
                    ];
            
                    DB::table('activity_logs')->insert($activityLog1);

                }else{
                    
                    if($result == $allBet->bet_type){
                        if($result == 'meron'){
                            $reverseWinBet = $allBet->amount * $meronPayout;
                        }
                        elseif($result == 'wala'){
                            $reverseWinBet = $allBet->amount * $walaPayout;
                        }
                        else{
                            if($allBet->amount >= 1000){
                                $extra = $allBet->amount - 1000;
                                $reverseWinBet = 8000 + $extra;
                            }else{
                                $reverseWinBet = $allBet->amount * 8;
                            }
                        }

                        $totalBalance = $user[0]->current_balance + round($reverseWinBet);
                        
                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('transactions')
                        ->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('transaction_type', 'result')
                        ->update([
                            'status' => 3, 
                        ]);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => round($reverseWinBet),
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'WIN: result: ' . $result . ' = Bet: ' . $allBet->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'reverse',
                            'amount' => 0,
                            'current_balance' => $totalBalance,
                            'status' => 3,
                            'note' => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 

                        DB::table('activity_logs')->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);

                        $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you won ' . round($reverseWinBet);
                        $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you won ' . round($reverseWinBet);
                    
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
                    
                        DB::table('activity_logs')->insert($activityLog);

                        $activityLog1 = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description1,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog1);
                    }else{

                        $currBalance = DB::table('users')->select('id','current_balance')->where('id', $allBet->user_id)->first();
    
                        DB::table('transactions')
                        ->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('transaction_type', 'result')
                        ->update([
                            'status' => 3, 
                        ]);
    
                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => 0,
                            'current_balance' => $currBalance->current_balance,
                            'status' => 2,
                            'note'     => 'LOSE: result: ' . $result . ' = Bet: ' . $allBet->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 
    
                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'reverse',
                            'amount' => 0,
                            'current_balance' => $totalBalance,
                            'status' => 4,
                            'note' => 'REVERSE: result from ' . $lastResult . ' to ' . $result,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'request_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 
    
                        DB::table('activity_logs')->where('user_id', $allBet->user_id)
                        ->where('betting_id', $allBet->id)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);
    
                        $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you lose ' . $allBet->amount;
                        $description1  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' you lose ' . $allBet->amount;
                    
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog);
    
                        $activityLog1 = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description1,
                            'date_time'   => $todayDate,
                        ];
                
                        DB::table('activity_logs')->insert($activityLog1);
                    }
                
                }

                DB::table('bettings')->where('id', $allBet->id)->update([
                    'result' => $result,
                    'result_date_time' => $todayDate,
                ]);
            }

            $description  = 'Reverse: Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' result from ' . $lastResult . ' to ' . $result;

            $activityLog = [
                'user_id'        => $allBet->user_id,
                'description' => $description,
                'date_time'   => $todayDate,
            ];
                
            DB::table('activity_logs')->insert($activityLog);


            DB::table('fights')->where('id', $fightId)->update([
                'result' => $result,
                'result_date_time' => $todayDate,
            ]);

        }

        return response()->json(array("success"=>true));

        
    }

    public function edit($id)
    {
        $event = Event::find($id);
        return response()->json($event);
    }

    public function showFightBet($id)
    {
        $fightId = $id;
        $allBets = collect([]);
        $isWin = 0;
       
        $selectedFight = DB::table('fights')->where('id',  $fightId)->get();

        $selectedEvent = DB::table('events')->where('id',  $selectedFight[0]->event_id)->get();

        $selectedBettings = DB::table('bettings')->where('fight_id',  $fightId)->get();

        $meronPayout = $selectedFight[0]->payoutMeron;
        $walaPayout = $selectedFight[0]->payoutWala;
        $palasada = $selectedEvent[0]->palasada;
        $result = $selectedFight[0]->result;

        foreach($selectedBettings as $selectedBetting){
            $user = DB::table('users')
            ->select('user_name','current_balance','role_type')
            ->where('id',  $selectedBetting->user_id)->get();

            $totalReward = 0;
            $totalPalasada = 0;
            $totalGrossWinning = 0;
            $totalNet = 0;

            $totalPalasada = $palasada * $selectedBetting->amount;

            if($result == 'cancel' || $result == null){
                $allBets->push([
                    'id' => $fightId,
                    'role_type' => $user[0]->role_type,
                    'local_date' => $selectedBetting->bet_date_time,
                    'user_name' => $user[0]->user_name,
                    'bet' => $selectedBetting->bet_type,
                    'result' => $result,
                    'amount' => $selectedBetting->amount,
                    'palasada' => 0.00,
                    'isWin' => $isWin,
                    'gross_winning' => 0.00,
                    'net' => 0.00,
                    'current_balance' => $user[0]->current_balance,
                ]);
            }else{
                if($selectedBetting->result == $selectedBetting->bet_type){
                    $isWin = 1;
                }else{
                    $isWin = 2;
                }

                if($selectedBetting->result == 'meron'){
                    $totalGrossWinning = $selectedBetting->amount * $meronPayout;
                }else if($selectedBetting->result == 'wala'){
                    $totalGrossWinning = $selectedBetting->amount * $walaPayout;
                }else{
                    if($selectedBetting->amount >= 1000){
                        $extra = $selectedBetting->amount - 1000;
                        $totalGrossWinning = 8000 + $extra;
                    }else{
                        $totalGrossWinning = $selectedBetting->amount * 8;
                    }
                }

                $totalNet = $totalGrossWinning - $totalPalasada;
    
                $allBets->push([
                    'id' => $fightId,
                    'role_type' => $user[0]->role_type,
                    'local_date' => $selectedBetting->bet_date_time,
                    'user_name' => $user[0]->user_name,
                    'bet' => $selectedBetting->bet_type,
                    'result' => $selectedBetting->result,
                    'amount' => $selectedBetting->amount,
                    'palasada' => $totalPalasada,
                    'isWin' => $isWin,
                    'gross_winning' => $totalGrossWinning,
                    'net' => $totalNet,
                    'current_balance' => $user[0]->current_balance,
                ]);
            }

        }
       
        $allBets->all();


        return view('operators.events.show_fight', compact('allBets','selectedEvent','selectedFight'));
    }

    public function showActiveFightBet($id)
    {
        $eventId = $id;
        $allBets = collect([]);
        $isWin = 0;
       

        $selectedEvent = DB::table('events')->where('id',  $eventId)->get();

        $selectedFight = DB::table('fights')->where('event_id',  $selectedEvent[0]->id)->where('status', 0)->get();

        $selectedBettings = DB::table('bettings')->where('fight_id',  $selectedFight[0]->id)->get();

        $meronPayout = $selectedFight[0]->payoutMeron;
        $walaPayout = $selectedFight[0]->payoutWala;
        $palasada = $selectedEvent[0]->palasada;

        foreach($selectedBettings as $selectedBetting){
            $user = DB::table('users')->select('user_name','current_balance','role_type')->where('id',  $selectedBetting->user_id)->get();

            $totalReward = 0;
            $totalPalasada = 0;
            $totalGrossWinning = 0;
            $totalNet = 0;


            $totalPalasada = $palasada * $selectedBetting->amount;

            if($selectedBetting->result == 'cancel' || $selectedBetting->result == null){
                $allBets->push([
                    'id' => $selectedFight[0]->id,
                    'role_type' => $user[0]->role_type,
                    'local_date' => $selectedBetting->bet_date_time,
                    'user_name' => $user[0]->user_name,
                    'bet' => $selectedBetting->bet_type,
                    'result' => $selectedBetting->result,
                    'amount' => $selectedBetting->amount,
                    'palasada' => 0.00,
                    'isWin' => $isWin,
                    'gross_winning' => 0.00,
                    'net' => 0.00,
                    'current_balance' => $user[0]->current_balance,
                ]);
            }else{
                if($selectedBetting->result == $selectedBetting->bet_type){
                    $isWin = 1;
                }else{
                    $isWin = 2;
                }

                if($selectedBetting->result == 'meron'){
                    $totalGrossWinning = $selectedBetting->amount * $meronPayout;
                }else if($selectedBetting->result == 'wala'){
                    $totalGrossWinning = $selectedBetting->amount * $walaPayout;
                }else{
                    if($selectedBetting->amount >= 1000){
                        $extra = $selectedBetting->amount - 1000;
                        $totalGrossWinning = 8000 + $extra;
                    }else{
                        $totalGrossWinning = $selectedBetting->amount * 8;
                    }
                }

                $totalNet = $totalGrossWinning - $totalPalasada;
    
                $allBets->push([
                    'id' => $selectedFight[0]->id,
                    'role_type' => $user[0]->role_type,
                    'local_date' => $selectedBetting->bet_date_time,
                    'user_name' => $user[0]->user_name,
                    'bet' => $selectedBetting->bet_type,
                    'result' => $selectedBetting->result,
                    'amount' => $selectedBetting->amount,
                    'palasada' => $totalPalasada,
                    'isWin' => $isWin,
                    'gross_winning' => $totalGrossWinning,
                    'net' => $totalNet,
                    'current_balance' => $user[0]->current_balance,
                ]);
            }

        }
       
        $allBets->all();


        return view('operators.events.show_fight', compact('allBets','selectedEvent','selectedFight'));
    }
}
