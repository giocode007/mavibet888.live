<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Fight;
use App\Models\Status;
use App\Models\Betting;
use App\Events\BetUpdated;
use App\Events\FightUpdated;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Events\ResultUpdated;
use App\Events\StatusUpdated;
use App\Events\BalanceUpdated;
use Illuminate\Support\Facades\DB;
use App\Events\RefreshUsersUpdated;
use Illuminate\Support\Facades\Auth;

class ArenaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index($id){
        $activeEvent = DB::table('events')->where('id', $id)->where('status', 1)->count();
        $activeEventAndFight = DB::table('fights')->where('event_id', $id)->where('status', 0)->count();

        if($activeEventAndFight == 0){
            $lastFight = DB::table('fights')->select('id')->where('event_id', $id)->orderBy('id', 'desc')->first();

            if($lastFight != null){
                DB::table('fights')->where('id', $lastFight->id)->update([
                    'status' => 0
                ]);
            }else{
                return redirect('error.404');
            }
            
        }

        if($activeEvent != 0 ){
            $userId = Auth::user()->id;

            $event = DB::table('events')->where('id', $id)->get();

            $fight = DB::table('fights')->where('event_id', $id)->where('status', 0)->get();
            $lastFight = DB::table('fights')->where('event_id', $id)->where('status', 1)->orderBy('id', 'desc')->first();

            $bets = DB::table('bettings')->where('user_id', $userId)->where('fight_id', $fight[0]->id)->where('status', 0)->get();
            $allBets = DB::table('bettings')->select('amount','bet_type')->where('fight_id', $fight[0]->id)->where('status', 0)->get();

            $status = DB::table('status')->pluck('status_type', 'id');
            $selectedStatus = DB::table('status')->select('id', 'status_type')->where('status', 1)->get();

            $cancelFight = DB::table('fights')->where('result', 'cancel')->where('event_id', $id)->pluck('id','fight_number');
            $selectedCancelFight= DB::table('fights')->select('id','fight_number')->where('result', 'cancel')->where('event_id', $id)->first();

            $meronBet = 0;
            $walaBet = 0;
            $drawBet = 0;
            $allMeronBet = 0;
            $allWalaBet = 0;
            $allRealMeronBet = 0;
            $allRealWalaBet = 0;
            $allDrawBet = 0;
            $meronPayout = 0;
            $walaPayout = 0;
            $total = 0;
            
            foreach($bets as $bet){
                if($bet->bet_type == 'meron'){
                    $meronBet += $bet->amount;
                }else if($bet->bet_type == 'wala'){
                    $walaBet += $bet->amount;
                }else{
                    $drawBet += $bet->amount;
                }
            }

            foreach($allBets as $allBet){
                if($allBet->bet_type == 'meron'){
                    $allMeronBet += $allBet->amount;
                }else if($allBet->bet_type == 'wala'){
                    $allWalaBet += $allBet->amount;
                }else{
                    $allDrawBet += $allBet->amount;
                }
            }

            $allBetsExceptAdmin = DB::table('bettings')
            ->select('amount','bet_type')
            ->where('fight_id', $fight[0]->id)
            ->where('status', 0)
            ->where('role_type', 'Player')
            ->get();
        
            foreach($allBetsExceptAdmin as $allBet){
                if($allBet->bet_type == 'meron'){
                    $allRealMeronBet += $allBet->amount;
                }else if($allBet->bet_type == 'wala'){
                    $allRealWalaBet += $allBet->amount;
                }
            }

            $total = $allMeronBet + $allWalaBet;

            if($allMeronBet > 0){
                $meronPayout = $total / $allMeronBet; 
                $meronPayout  = $meronPayout - ($meronPayout * $event[0]->palasada);
                $meronPayout = $meronPayout * 100;
            }
            
            if($allWalaBet > 0){
                $walaPayout = $total / $allWalaBet; 
                $walaPayout  = $walaPayout - ($walaPayout * $event[0]->palasada);
                $walaPayout = $walaPayout * 100;
            }
            
            return view('arena', compact('allRealWalaBet','allRealMeronBet','selectedCancelFight','cancelFight','lastFight','fight','selectedStatus','status','event','meronBet','walaBet','drawBet','allMeronBet','allWalaBet','allDrawBet','meronPayout','walaPayout'));
        }else{
            return redirect('error.404');
        }

    }

    public function bet(Request $request){

        if(($request->amount <= Auth::user()->current_balance && $request->amount >= 20) 
        || (Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator')){

            $userId = Auth::user()->id;
            $roleType = Auth::user()->role_type;

            $event = DB::table('events')->where('id', $request->eventId)->get();

            $dt         = Carbon::now('Asia/Manila');
            $todayDate  = $dt->toDayDateTimeString();

            $totalBalance = Auth::user()->current_balance - $request->amount;    

            $bet = Betting::updateOrCreate([
                'id' => $request->bet_id
            ],
            [
                'user_id' => $userId,
                'fight_id' => $request->id,
                'role_type' => $roleType,
                'amount'     => $request->amount,
                'bet_type' => $request->bet_type,
                'bet_date_time' => $todayDate,
                'status' => 0,
            ]); 


            User::updateOrCreate([
                'id' => $userId
            ],
            [
                'current_balance' => $totalBalance,
            ]);  

            $user = DB::table('users')->select('user_name', 'current_balance','agent_code')->where('id', $userId)->get();

            $bets = DB::table('bettings')->where('user_id', $userId)->where('fight_id', $request->id)->where('status', 0)->get();
            $allBets = DB::table('bettings')->select('id','amount','bet_type')->where('fight_id', $request->id)->where('status', 0)->get();

            $meronBet = 0;
            $walaBet = 0;
            $drawBet = 0;
            $allMeronBet = 0;
            $allWalaBet = 0;
            $allRealMeronBet = 0;
            $allRealWalaBet = 0;
            $allDrawBet = 0;
            $meronPayout = 0;
            $walaPayout = 0;
            $total = 0;
            
            foreach($bets as $bet){
                if($bet->bet_type == 'meron'){
                    $meronBet += $bet->amount;
                }else if($bet->bet_type == 'wala'){
                    $walaBet += $bet->amount;
                }else{
                    $drawBet += $bet->amount;
                }
            }

            foreach($allBets as $allBet){
                if($allBet->bet_type == 'meron'){
                    $allMeronBet += $allBet->amount;
                }else if($allBet->bet_type == 'wala'){
                    $allWalaBet += $allBet->amount;
                }else{
                    $allDrawBet += $allBet->amount;
                }
            }

            $fight = DB::table('fights')->where('event_id', $request->eventId)->where('status', 0)->get();

            $transaction = Transactions::updateOrCreate([
                'id' => $request->transaction_id
            ],
            [
                'user_id' => $userId,
                'betting_id' => $allBet->id,
                'transaction_type' => 'betting',
                'amount' => $request->amount,
                'current_balance' => $totalBalance,
                'status' => 2,
                'note'     => 'Bet on ' . $request->bet_type,
                'from' => Auth::user()->user_name,
                'to' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                'request_date_time' => $todayDate,
                'approved_date_time' => $todayDate,
                'approve_by' => Auth::user()->user_name,

            ]);    

            $description  = 'Bet ' . $request->amount . ' on fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name;
    
            $activityLog = [
                'user_id'        => Auth::user()->id,
                'status'        => 1,
                'description' => $description,
                'date_time'   => $todayDate,
            ];

            DB::table('activity_logs')->insert($activityLog);

            $allBetsExceptAdmin = DB::table('bettings')->select('amount','bet_type')->where('fight_id', $fight[0]->id)->where('status', 0)
            ->where('role_type', 'Player')
            ->get();
        
            foreach($allBetsExceptAdmin as $allBet){
                if($allBet->bet_type == 'meron'){
                    $allRealMeronBet += $allBet->amount;
                }else if($allBet->bet_type == 'wala'){
                    $allRealWalaBet += $allBet->amount;
                }
            }


            $total = $allMeronBet + $allWalaBet;

            if($allMeronBet > 0){
                $meronPayout = $total / $allMeronBet; 
                $meronPayout  = $meronPayout - ($meronPayout * $event[0]->palasada);
                $meronPayout = $meronPayout * 100;
            }
            
            if($allWalaBet > 0){
                $walaPayout = $total / $allWalaBet; 
                $walaPayout  = $walaPayout - ($walaPayout * $event[0]->palasada);
                $walaPayout = $walaPayout * 100;
            }
            
            
            event(new BetUpdated($userId, $user[0]->user_name, $allBet->bet_type, $allBet->amount, $roleType, $allMeronBet, $allWalaBet, $allDrawBet, $meronPayout, $walaPayout, $allRealMeronBet, $allRealWalaBet));

            return response()->json(array('success'=>true,$bet,$user,$meronBet,$walaBet,$drawBet,$meronPayout,$walaPayout));
        }else{
            return response()->json();
        }
    }


    public function changeStatus(Request $request)
    {
        $id = $request->id;

        DB::table('status')->where('status', 1)->update(['status' => 0]);

        DB::table('status')->where('id', $id)->update(['status' => 1]);

        $selectedStatus = DB::table('status')->select('id', 'status_type')->where('status', 1)->get();

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();
        $description  = 'change status to ' . $selectedStatus[0]->status_type;
    
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);
        
        event(new StatusUpdated($selectedStatus[0]->status_type));

        return response()->json();
    }

    public function fightStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        DB::table('fights')->where('id', $id)->update(['isOpen' => $status]);

        $fight = DB::table('fights')->select('id', 'isOpen', 'fight_number', 'event_id')->where('id', $id)->get();
        $event = DB::table('events')->select('event_name')->where('id', $fight[0]->event_id)->first();

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();
        $description = '';
        if($fight[0]->isOpen == 1){
            $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' is open';
        }else{
            $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event->event_name . ' is close';
        }
    
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);
        
        event(new FightUpdated($fight[0]->isOpen));

        return response()->json(array('isOpen'=>$fight[0]->isOpen));
    }

    public function checkFight(Request $request)
    {
        $id = $request->id;

        $fight = DB::table('fights')->select('id', 'isOpen', 'result')->where('id', $id)->get();

        return response()->json(array('isOpen'=>$fight[0]->isOpen,'result'=>$fight[0]->result));
    }

    public function fightResult(Request $request)
    {
        $id = $request->id;
        $result = $request->result;
        $payoutMeron = $request->payoutMeron;
        $payoutWala = $request->payoutWala;
        $eventId = $request->eventId;

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        DB::table('fights')->where('id', $id)->update([
            'result' => $result, 
            'payoutMeron' => $payoutMeron, 
            'payoutWala' => $payoutWala,
            'declared_by' => Auth::user()->user_name,
            'result_date_time' => $todayDate,
        ]);

        $fight = DB::table('fights')->select('id', 'result', 'fight_number')->where('id', $id)->get();

        $resultData = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'asc')->get();

        $description  = 'Fight # ' . $fight[0]->fight_number . ' result ' . $fight[0]->result;
    
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);

        event(new ResultUpdated($fight[0]->id,$fight[0]->result,'null',$fight[0]->fight_number,true,$resultData));

        return response()->json(array('result'=>$fight[0]->result));
    }

    public function resetAll(Request $request){
        $id = $request->id;
        $eventId = $request->eventId;

        $fight = DB::table('fights')->select('id', 'event_id','fight_number', 'result', 'payoutMeron', 'payoutWala')
        ->where('id', $id)->get();
        
        $event = DB::table('events')->select('event_name')->where('id', $eventId)->get();

        $allBets = DB::table('bettings')->select('id', 'user_id', 'amount')->where('fight_id', $id)->get();

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();
        $meronPayout = $fight[0]->payoutMeron;
        $walaPayout = $fight[0]->payoutWala;
        
        foreach($allBets as $allBet){
            $user = DB::table('users')->select('id', 'current_balance', 'agent_code', 'user_name', 'role_type')->where('id', $allBet->user_id)->get();
            $checkBet = DB::table('bettings')->select('id', 'status', 'bet_type')->where('id', $allBet->id)->get();


            if($checkBet[0]->status == 0){

                //Commission
                if($user[0]->role_type == 'Player'){
                    if($fight[0]->result != 'cancel' && $fight[0]->result != 'draw'){
                        if($checkBet[0]->bet_type == 'meron' || $checkBet[0]->bet_type == 'wala'){
    
                            $checkAgent = DB::table('users')->select('id','user_name','current_commission', 
                            'commission_percent', 'current_balance', 'player_code', 'agent_code', 'role_type')
                            ->where('player_code', $user[0]->agent_code)->get();
            
                            if($checkAgent[0]->role_type == 'Sub_Admin'){
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
                                    'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                                    'from' => $user[0]->user_name,
                                    'to' => $checkAgent[0]->user_name,
                                    'approved_date_time' => $todayDate,
                                    'approve_by' => Auth::user()->user_name,
                                ]);   
                
                                $description  = 'Received ' . $commission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                    
                                $activityLog = [
                                    'user_id'        => $checkAgent[0]->id,
                                    'betting_id' => $allBet->id,
                                    'status' => 1,
                                    'description' => $description,
                                    'date_time'   => $todayDate,
                                ];
                
                                DB::table('activity_logs')->insert($activityLog);
                            }else{
                                if($checkAgent[0]->role_type == 'Sub_Operator'){
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission', 'current_balance', 'commission_percent', 
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                                        'from' => $checkAgent[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                    
                                    $description  = 'Received ' . $masterCommission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description1 = 'Received ' . $totalSubOpCommission . ' Commission from ' . $checkAgent[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                        
                                    $activityLog = [
                                        'user_id'        => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id'        => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);
                                }else if($checkAgent[0]->role_type == 'Master_Agent'){
                                    $checkMaster = DB::table('users')
                                    ->select('id','user_name','current_commission', 'current_balance', 'commission_percent', 
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission',  'current_balance', 'commission_percent', 
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                                        'from' => $checkMaster[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                    
                                    $description  = 'Received ' . $totalGoldCommission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description1 = 'Received ' . $totalMasterCommission . ' Commission from ' . $checkAgent[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description2 = 'Received ' . $totalSubOpCommission . ' Commission from ' . $checkMaster[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                        
                                    $activityLog = [
                                        'user_id'        => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id'        => $checkMaster[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);
            
                                    $activityLog2 = [
                                        'user_id'        => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description2,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog2);
                                }else{
                                    $checkMaster = DB::table('users')
                                    ->select('id','user_name','current_commission', 'current_balance', 'commission_percent', 
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkAgent[0]->agent_code)->get();
                                    $checkSubOp = DB::table('users')
                                    ->select('id','user_name','current_commission',  'current_balance', 'commission_percent', 
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkMaster[0]->agent_code)->get();
                                    $checkSubAdmin = DB::table('users')
                                    ->select('id','user_name','current_commission',  'current_balance', 'commission_percent', 
                                    'player_code', 'agent_code', 'role_type')
                                    ->where('player_code', $checkSubOp[0]->agent_code)->get();
            
            
                                    $totalGoldCommission = $allBet->amount * $checkAgent[0]->commission_percent;
                                    $masterCommission = $allBet->amount * $checkMaster[0]->commission_percent;
                                    $subOpCommission = $allBet->amount * $checkSubOp[0]->commission_percent;
                                    $subAdminCommission = $allBet->amount * $checkSubAdmin[0]->commission_percent;
                
                                    
                                    $totalMasterCommission = $masterCommission - $totalGoldCommission;
                                    $totalSubOpCommission = $subOpCommission - $masterCommission;
                                    $totalSubAdminCommission = $subAdminCommission - $subOpCommission;
            
                                    $commissionGold = $totalGoldCommission + $checkAgent[0]->current_commission;
                                    $commissionMaster = $totalMasterCommission + $checkMaster[0]->current_commission;
                                    $commissionSubOp = $totalSubOpCommission + $checkSubOp[0]->current_commission;
                                    $commissionSubAdmin = $totalSubAdminCommission + $checkSubAdmin[0]->current_commission;
                
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

                                    DB::table('users')->where('id', $checkSubAdmin[0]->id)
                                    ->update([
                                        'current_commission' => $commissionSubAdmin,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
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
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                                        'from' => $checkMaster[0]->user_name,
                                        'to' => $checkSubOp[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]); 
                                    
                                    $transaction = Transactions::updateOrCreate([
                                        'id' => $request->transaction_id
                                    ],
                                    [
                                        'user_id' => $checkSubAdmin[0]->id,
                                        'betting_id' => $allBet->id,
                                        'transaction_type' => 'commission',
                                        'amount' => $totalSubAdminCommission,
                                        'current_balance' => $checkSubAdmin[0]->current_balance,
                                        'current_commission' => $commissionSubAdmin,
                                        'status' => 1,
                                        'note'     => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                                        'from' => $checkSubOp[0]->user_name,
                                        'to' => $checkSubAdmin[0]->user_name,
                                        'approved_date_time' => $todayDate,
                                        'approve_by' => Auth::user()->user_name,
                                    ]);   
                    
                                    $description  = 'Received ' . $totalGoldCommission . ' Commission from ' . $user[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description1 = 'Received ' . $totalMasterCommission . ' Commission from ' . $checkAgent[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description2 = 'Received ' . $totalSubOpCommission . ' Commission from ' . $checkMaster[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                                    $description3 = 'Received ' . $totalSubAdminCommission . ' Commission from ' . $checkSubOp[0]->user_name . ' / ' . $fight[0]->fight_number  . ' - ' . $event[0]->event_name;
                        
                                    $activityLog = [
                                        'user_id'        => $checkAgent[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog);
                
                                    $activityLog1 = [
                                        'user_id'        => $checkMaster[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description1,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog1);
            
                                    $activityLog2 = [
                                        'user_id'        => $checkSubOp[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description2,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog2);

                                    $activityLog3 = [
                                        'user_id'        => $checkSubAdmin[0]->id,
                                        'betting_id' => $allBet->id,
                                        'status' => 1,
                                        'description' => $description3,
                                        'date_time'   => $todayDate,
                                    ];
                    
                                    DB::table('activity_logs')->insert($activityLog3);
                                }
                            }
                        }
                    }
                }
                
                $totalBalance = 0;
                $reward = 0;
                $extra = 0;

                if($fight[0]->result == 'draw'){

                    if($fight[0]->result == $checkBet[0]->bet_type){
                        
                        if($allBet->amount >= 1000){
                            $extra = $allBet->amount - 1000;
                            $reward = 8000 + $extra;
                        }else{
                            $reward = $allBet->amount * 8;
                        }

                        $totalBalance = $user[0]->current_balance + round($reward);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => $reward,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'WIN: result: ' . $fight[0]->result . ' = Bet: ' . $checkBet[0]->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                            'to' => $user[0]->user_name,
                            'request_date_time' => $todayDate,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
    
                        ]); 
    
                        $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' you won ' . $reward;
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
    
                        DB::table('activity_logs')->insert($activityLog);
                    }else{
                        $reward = $allBet->amount;

                        $totalBalance = $user[0]->current_balance + round($reward);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => $reward,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'DRAW: ' . $reward . ' points returned',
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                            'to' => $user[0]->user_name,
                            'request_date_time' => $todayDate,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
    
                        ]); 
    
                        $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' result draw, ' . $reward . ' points returned';
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
    
                        DB::table('activity_logs')->insert($activityLog);
                    }

            
                    DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                    DB::table('bettings')->where('id', $allBet->id)->update([
                        'status' => 1,
                        'result' => $fight[0]->result,
                        'result_date_time' => $todayDate,
                    ]);

                }
                elseif($fight[0]->result == $checkBet[0]->bet_type || $fight[0]->result == 'cancel'){

                    if($fight[0]->result == 'meron'){
                        $reward = $allBet->amount * $meronPayout;

                        $totalBalance = $user[0]->current_balance + round($reward);
            
                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('bettings')->where('id', $allBet->id)->update([
                            'status' => 1,
                            'result' => $fight[0]->result,
                            'result_date_time' => $todayDate,
                        ]);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => $reward,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'WIN: result: ' . $fight[0]->result . ' = Bet: ' . $checkBet[0]->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                            'to' => $user[0]->user_name,
                            'request_date_time' => $todayDate,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,

                        ]); 

                        $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' you won ' . $reward;
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];

                        DB::table('activity_logs')->insert($activityLog);
                    }
                    elseif($fight[0]->result == 'wala'){
                        $reward = $allBet->amount * $walaPayout;

                        $totalBalance = $user[0]->current_balance + round($reward);
            
                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('bettings')->where('id', $allBet->id)->update([
                            'status' => 1,
                            'result' => $fight[0]->result,
                            'result_date_time' => $todayDate,
                        ]);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => $reward,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'WIN: result: ' . $fight[0]->result . ' = Bet: ' . $checkBet[0]->bet_type,
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                            'to' => $user[0]->user_name,
                            'request_date_time' => $todayDate,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,

                        ]); 

                        $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' you won ' . $reward;
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];

                        DB::table('activity_logs')->insert($activityLog);
                    }
                    else{
                        $reward = $allBet->amount;

                        $totalBalance = $user[0]->current_balance + round($reward);
            
                        DB::table('users')->where('id', $allBet->user_id)->update(['current_balance' => $totalBalance ]);

                        DB::table('bettings')->where('id', $allBet->id)->update([
                            'status' => 1,
                            'result' => $fight[0]->result,
                            'result_date_time' => $todayDate,
                        ]);

                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'transaction_type' => 'result',
                            'amount' => $reward,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => 'CANCEL: ' . $reward . ' points returned',
                            'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                            'to' => $user[0]->user_name,
                            'request_date_time' => $todayDate,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,

                        ]); 

                        $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' result cancel, ' . $reward . ' points returned';
                
                        $activityLog = [
                            'user_id'        => $allBet->user_id,
                            'betting_id' => $allBet->id,
                            'status' => 1,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];

                        DB::table('activity_logs')->insert($activityLog);
                    }

                    
                }else{
                    $totalBalance = $user[0]->current_balance;

                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'transaction_type' => 'result',
                        'amount' => $reward,
                        'current_balance' => $totalBalance,
                        'status' => 2,
                        'note'     => 'LOSE: result: ' . $fight[0]->result . ' = Bet: ' . $checkBet[0]->bet_type,
                        'from' => 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name,
                        'to' => $user[0]->user_name,
                        'request_date_time' => $todayDate,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,

                    ]); 

                    $loseBet = DB::table('bettings')->where('id', $allBet->id)
                    ->update([
                        'status' => 1, 
                        'result' => $fight[0]->result, 
                        'result_date_time' => $todayDate,
                    ]);

                    $description  = 'Fight # ' . $fight[0]->fight_number . ' - ' . $event[0]->event_name . ' you lose ' . $allBet->amount;
            
                    $activityLog = [
                        'user_id'        => $allBet->user_id,
                        'betting_id' => $allBet->id,
                        'status' => 1,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];

                    DB::table('activity_logs')->insert($activityLog);
                }

                event(new BalanceUpdated($allBet->user_id,round($reward)));
            }
            
        }


        $fightData['data'] = DB::table('fights')->select('id','fight_number')->where('result', 'cancel')->where('event_id', $eventId)->get();
  
        return response()->json(array($fightData));
    }

    public function checkResult(Request $request)
    {
        $id = $request->id;
        $eventId = $request->eventId;
        
        $resultData = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'asc')->get();

        return response()->json($resultData);
    }

    public function checkBet(Request $request)
    {
        $fightId = $request->fightId;
        
        $allBets = collect([]);
        
        $resultData = DB::table('bettings')
        ->select('user_id','bet_type', 'amount')
        ->where('fight_id', $fightId)
        ->where('status', 0)
        ->orderBy('id', 'asc')->get();

        foreach($resultData as $data){
            $user = DB::table('users')->select('user_name')
                ->where('id',  $data->user_id)->first();

            $allBets->push([
                'user_name' => $user->user_name,
                'bet_type' => $data->bet_type,
                'amount' => $data->amount,
            ]);
        }

        $allBets->all();
        
        if(Auth::user()->role_type == 'Operator' || Auth::user()->role_type == 'Declarator'){
            return response()->json($allBets);
        }else{
            return response()->json();
        }
    }

    public function goNext(Request $request)
    {
        $eventId = $request->eventId;

        DB::table('fights')->where('id', $request->id)->update([
            'status' => 1, 
        ]);
        
        $lastFight = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'desc')->first();

        DB::table('fights')->where('id', $lastFight->id)->update([
            'isOpen' => 0, 
        ]);

        if($request->actions == 'goNext'){
            $fightNumber = $lastFight->fight_number + 1;

            $createFight = Fight::Create(
            [
                'event_id' => $eventId,
                'fight_number' => $fightNumber,
                'payoutMeron' => 0,
                'payoutWala' => 0,
                'isOpen' => 0,
                'status' => 0,
            ]); 

            $fight = DB::table('fights')->where('event_id', $eventId)->where('status', 0)->get();

            $resultData = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'asc')->get();

            event(new ResultUpdated($fight[0]->id,$lastFight->result,$lastFight->fight_number,$fight[0]->fight_number,false,$resultData));

        }else{
            DB::table('fights')->where('id', $request->cancelFight)->update([
                'result' => NULL, 
                'status' => 0, 
            ]);

            $cancelFight = DB::table('fights')->select('id', 'fight_number', 'result')
                                ->where('id', $request->cancelFight)->get();

            $resultData = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'asc')->get();
            
            event(new ResultUpdated($cancelFight[0]->id,$lastFight->result,$lastFight->fight_number,$cancelFight[0]->fight_number,false,$resultData));
        }

        $resultData = DB::table('fights')->where('event_id', $eventId)->where('status', 1)->orderBy('id', 'asc')->get();

        return response()->json($resultData);
    }


    public function refreshUsers(Request $request)
    {
        $userId = Auth::user()->id;

        DB::table('events')->where('id', $request->eventId)->update(['status' => 2]);

        $event = DB::table('events')->select('event_name')->where('id', $request->eventId)->get();

        $openFight = DB::table('fights')->select('id')->where('event_id', $request->eventId)->get();

        foreach($openFight as $fight){
            DB::table('fights')->where('id', $fight->id)->update(['isOpen' => 0]);
        }

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        $description  = 'Refresh Users and complete event ' . $event[0]->event_name;
            
        $activityLog = [
            'user_id'        => $userId,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);

        event(new RefreshUsersUpdated(true,$userId));
    }
}
