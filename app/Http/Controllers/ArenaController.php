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
use Illuminate\Support\Facades\Auth;

class ArenaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index($id){

        if(Auth::user()->role_type == 'Operator' && DB::table('fights')->where('event_id', $id)->count() == 0){
            $transaction = Fight::Create(
            [
                'event_id' => $id,
                'fight_number' => 1,
                'payoutMeron' => 0,
                'payoutWala' => 0,
                'isOpen' => 0,
                'status' => 0,
            ]);     
        }

        $event = DB::table('events')->where('id', $id)->get();

        $fight = DB::table('fights')->where('event_id', $id)->where('status', 0)->get();
        $lastFight = DB::table('fights')->where('event_id', $id)->where('status', 1)->orderBy('id', 'desc')->first();

        $userId = Auth::user()->id;

        $activeFight = DB::table('fights')->where('status', 0)->get();
        $bets = DB::table('bettings')->where('user_id', $userId)->where('fight_id', $activeFight[0]->id)->where('status', 0)->get();
        $allBets = DB::table('bettings')->select('amount','bet_type')->where('fight_id', $activeFight[0]->id)->where('status', 0)->get();

        $status = DB::table('status')->pluck('status_type', 'id');
        $selectedStatus = DB::table('status')->select('id', 'status_type')->where('status', 1)->get();

        $meronBet = 0;
        $walaBet = 0;
        $drawBet = 0;
        $allMeronBet = 0;
        $allWalaBet = 0;
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
        


        return view('arena', compact('lastFight','fight','selectedStatus','status','event','meronBet','walaBet','drawBet','allMeronBet','allWalaBet','allDrawBet','meronPayout','walaPayout'));
    }

    public function bet(Request $request){

        if(($request->amount <= Auth::user()->current_balance && $request->amount >= 20) || Auth::user()->role_type == 'Operator'){

            $userId = Auth::user()->id;

            $event = DB::table('events')->where('id', $request->eventId)->get();


            $dt         = Carbon::now('Asia/Manila');
            $todayDate  = $dt->toDayDateTimeString();

            $totalBalance = Auth::user()->current_balance - $request->amount;
    
            $transaction = Transactions::updateOrCreate([
                'id' => $request->transaction_id
            ],
            [
                'user_id' => $userId,
                'transaction_type' => 'betting',
                'amount' => $request->amount,
                'current_balance' => $totalBalance,
                'status' => 2,
                'note'     => $request->bet_type,
                'from' => Auth::user()->user_name,
                'to' => $request->id,
                'request_date_time' => $todayDate,
            ]);        

            $bet = Betting::updateOrCreate([
                'id' => $request->bet_id
            ],
            [
                'user_id' => $userId,
                'fight_id' => $request->id,
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

            $user = DB::table('users')->select('current_balance')->where('id', $userId)->get();

            $activeFight = DB::table('fights')->where('status', 0)->get();
            $bets = DB::table('bettings')->where('user_id', $userId)->where('fight_id', $activeFight[0]->id)->where('status', 0)->get();
            $allBets = DB::table('bettings')->select('amount','bet_type')->where('fight_id', $activeFight[0]->id)->where('status', 0)->get();
            
            $meronBet = 0;
            $walaBet = 0;
            $drawBet = 0;
            $allMeronBet = 0;
            $allWalaBet = 0;
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
            
            
            event(new BetUpdated($userId, $allMeronBet, $allWalaBet, $allDrawBet, $meronPayout, $walaPayout));

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

        event(new StatusUpdated($selectedStatus[0]->status_type));

        return response()->json();
    }

    public function fightStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        DB::table('fights')->where('id', $id)->update(['isOpen' => $status]);

        $fight = DB::table('fights')->select('id', 'isOpen')->where('id', $id)->get();
        
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

        DB::table('fights')->where('id', $id)->update([
            'result' => $result, 
            'payoutMeron' => $payoutMeron, 
            'payoutWala' => $payoutWala,
            'declared_by' => Auth::user()->user_name,
        ]);

        $fight = DB::table('fights')->select('id', 'result', 'fight_number')->where('id', $id)->get();

        event(new ResultUpdated($fight[0]->result,$fight[0]->fight_number,true));

        return response()->json(array('result'=>$fight[0]->result));
    }

    public function resetAll(Request $request){
        $id = $request->id;


        $fight = DB::table('fights')->select('id', 'result', 'payoutMeron', 'payoutWala')->where('id', $id)->get();
        
        $allBets = DB::table('bettings')->select('id', 'user_id', 'amount')->where('fight_id', $id)->get();
        
        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();
        $meronPayout = $fight[0]->payoutMeron;
        $walaPayout = $fight[0]->payoutWala;
        
        foreach($allBets as $allBet){
        
            $user = DB::table('users')->select('id', 'current_balance')->where('id', $allBet->user_id)->get();
            $checkBet = DB::table('bettings')->select('id', 'status', 'bet_type')->where('id', $allBet->id)->get();

            if($checkBet[0]->status == 0){
                $totalBalance = 0;
                $reward = 0;
                $extra = 0;

                if($fight[0]->result == $checkBet[0]->bet_type){
                    if($fight[0]->result == 'meron'){
                        $reward = $allBet->amount * $meronPayout;
                    }
                    elseif($fight[0]->result == 'wala'){
                        $reward = $allBet->amount * $walaPayout;
                    }
                    elseif($fight[0]->result == 'draw'){
                        if($allBet->amount >= 1000){
                            $extra = $allBet->amount - 1000;
                            $reward = 8000 + $extra;
                        }else{
                            $reward = $allBet->amount * 8;
                        }
                    }
                    else{
                        $reward = $allBet->amount;
                    }

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
                        'transaction_type' => 'betting',
                        'amount' => $reward,
                        'current_balance' => $totalBalance,
                        'status' => 1,
                        'note'     => $checkBet[0]->bet_type,
                        'from' => Auth::user()->user_name,
                        'to' => $id,
                        'request_date_time' => $todayDate,
                    ]); 
                }else{
                    $loseBet = DB::table('bettings')->where('fight_id', $id)->where('bet_type', '!=', $fight[0]->result)
                    ->update([
                        'status' => 1, 
                        'result' => $fight[0]->result, 
                        'result_date_time' => $todayDate,
                    ]);
                }

                event(new BalanceUpdated($allBet->user_id,round($reward)));
            }
            
        }
        
        
    }

    public function checkResetAll(Request $request)
    {
        $id = $request->id;
        
        $checkUnpaidBet = DB::table('bettings')->where('fight_id', $id)->where('status', 0)->count();

        return response()->json(array('checkUnpaidBet'=>$checkUnpaidBet));
    }

    public function goNext(Request $request)
    {
        $id = $request->id;
        $eventId = $request->eventId;
        $fightNumber = $request->fightNumber + 1;
        
        $fight = DB::table('fights')->where('id', $id)->get();

        $fightUpdate = DB::table('fights')->where('id', $id)->update([
            'status' => 1, 
        ]);

        $createFight = Fight::Create(
        [
            'event_id' => $eventId,
            'fight_number' => $fightNumber,
            'payoutMeron' => 0,
            'payoutWala' => 0,
            'isOpen' => 0,
            'status' => 0,
        ]); 

        event(new ResultUpdated($fight[0]->result,$fightNumber,false));

        $fight = DB::table('fights')->where('event_id', $eventId)->where('status', 0)->get();
        
        return response()->json(array('success'=>true,$fight[0]->id));

    }
}
