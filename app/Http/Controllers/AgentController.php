<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isAgents']);
    }


    public function dashboard()
    {
        $playerCode = Auth::user()->player_code;

        $agents = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type', '!=', 'Player')
        ->count();

        $activePlayers = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type',  'Player')
        ->where('status',  'Active')
        ->count();

        $disabledPlayers = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type',  'Player')
        ->where('status',  'Disabled')
        ->count();

        return view('agents.dashboard',compact('agents','activePlayers','disabledPlayers'));
    }


    public function loadLogs()
    {
        $playerId = Auth::user()->id;
        
        $selectedUser = DB::table('users')->select('id','user_name','role_type','agent_code')
        ->where('id',  $playerId)->get();

        $allTransactions = collect([]);

        $transactionHistory = DB::table('transactions')
        ->where('user_id', $playerId)
        ->orderBy('id', 'desc')
        ->get();


        foreach($transactionHistory as $history){

            if(($history->transaction_type != 'commission' && $history->transaction_type != 'commission get'
            && $history->transaction_type != 'commission out')){

                    $user = DB::table('users')->select('user_name')
                    ->where('id',  $history->user_id)->get();
        
                    $allTransactions->push([
                        'id' => $history->id,
                        'transaction_type' => $history->transaction_type,
                        'status' => $history->status,
                        'amount' => $history->amount,
                        'from' => $history->from,
                        'to' => $history->to,
                        'current_balance' => $history->current_balance,
                        'note' => $history->note,
                        'date' => $history->approved_date_time,
                    ]);
                
            }

        }
        
        $allTransactions->all();

        return view('agents.load_logs', compact('selectedUser','allTransactions'));
    }

    public function commissionLogs()
    {
        $userId = Auth::user()->id;
        
        $selectedUser = DB::table('users')->select('id','user_name','role_type','agent_code')
        ->where('id',  $userId)->first();

        $commissionHistory = collect([]);

        $transactionHistory = DB::table('transactions')
        ->where('user_id', $userId)
        ->orderBy('id', 'desc')
        ->get();

        foreach($transactionHistory as $history){

            if($history->transaction_type == 'commission' || $history->transaction_type == 'commission out'
            || $history->transaction_type == 'convert' || $history->transaction_type == 'commission get'){

                    if($history->status == 1){
                        $commissionHistory->push([
                            'id' => $history->id,
                            'transaction_type' => $history->transaction_type,
                            'status' => $history->status,
                            'amount' => $history->amount,
                            'from' => $history->from,
                            'to' => $history->to,
                            'current_commission' => $history->current_commission,
                            'note' => $history->note,
                            'date' => $history->approved_date_time,
                        ]);
                    }
                    
                }

        }
       
        $commissionHistory->all();
        
        return view('agents.commission_logs', compact('commissionHistory', 'selectedUser'));
    }

    public function getProfile($id)
    {
        $data = DB::table('users')->where('id', $id)->get();

        return view('agents.change_password', compact('data'));
    }
    
    // change password in db
    public function changeProfileInfo(Request $request)
    {

        if($request->current_password != null){
            $request->validate([
                'current_password' => ['required', new MatchOldPassword],
                'password'  => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
            ]);

            User::find(auth()->user()->id)->update(
            [
                'last_name'=> $request->last_name,
                'first_name'=> $request->first_name,
                'phone_number'=> $request->phone_number,
                'password'=> Hash::make($request->password),
            ]);
        }else{
            User::find(auth()->user()->id)->update(
            [
                'last_name'=> $request->last_name,
                'first_name'=> $request->first_name,
                'phone_number'=> $request->phone_number,
            ]);
            
        }

           

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        $description  = 'Change profile information';
                
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);

        Toastr::success('User change successfully :)','Success');
        return redirect()->intended('/dashboard');
    }

    public function getAgents()
    {
        $playerCode = Auth::user()->player_code;

        $agents = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type', '!=', 'Player')
        ->get();
        
        return view('agents.agents', compact('agents'));
    }

    public function getPlayerInfo(Request $request)
    {
        $playerId = $request->playerId;

        $playerInfo = DB::table('users')
        ->where('id', $playerId)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json(array($playerInfo));
    }

    public function updatePlayer(Request $request)
    {
        $playerId = $request->playerId;

        $playerInfo = DB::table('users')
        ->where('id', $playerId)
        ->get();

        $playerStatus = $request->playerStatus;
        $role = $request->role;

        if($playerInfo[0]->status != 'Banned'){
            if($request->currComm != null){

                $currComm = $request->currComm / 100;

                DB::table('users')->where('id', $playerId)
                ->update([
                    'commission_percent' => $currComm,
                    'status' => $playerStatus,
                ]);
        
                $dt         = Carbon::now('Asia/Manila');
                $todayDate  = $dt->toDayDateTimeString();
        
        
                $description  = 'Change player: (' . $playerInfo[0]->user_name . 
                ') Current Commission: ' . $playerInfo[0]->current_commission . ' to ' . $currComm . 
                ' Status: ' . $playerInfo[0]->status . ' to ' . $playerStatus;
            }else{
    
                if($playerInfo[0]->role_type != $role){
    
                    if(Auth::user()->role_type == 'Sub_Admin' ||
                        Auth::user()->role_type == 'Sub_Operator' || 
                            Auth::user()->role_type == 'Master_Agent'){
                        $code = Str::upper($this->generateRandomString(6));
    
                        DB::table('users')->where('id', $playerId)
                        ->update([
                            'role_type' => $role,
                            'status' => $playerStatus,
                            'player_code' => $code,
                        ]);
        
                        $dt         = Carbon::now('Asia/Manila');
                        $todayDate  = $dt->toDayDateTimeString();
        
        
                        $description  = 'Change player: (' . $playerInfo[0]->user_name . 
                        ') Role: ' . $playerInfo[0]->role_type . ' to ' . $role;
                    }
    
                }else{
                    
                    DB::table('users')->where('id', $playerId)
                    ->update([
                        'status' => $playerStatus,
                    ]);
        
                    $dt         = Carbon::now('Asia/Manila');
                    $todayDate  = $dt->toDayDateTimeString();
        
        
                    $description  = 'Change player: (' . $playerInfo[0]->user_name . 
                    ') Status: ' . $playerInfo[0]->status . ' to ' . $playerStatus;
                }
                
            }
            
            
            $activityLog = [
                'user_id'        => Auth::user()->id,
                'description' => $description,
                'date_time'   => $todayDate,
            ];
    
            DB::table('activity_logs')->insert($activityLog);
        }
        
        
    }

    public function generateRandomString(int $n=0)
    {
        $al = ['a','b','c','d','e','f','g','h','i','j','k'
        , 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u',
        'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E',
        'F','G','H','I','J','K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '2', '3', '4', '5', '6', '7', '8', '9'];

        $len = !$n ? random_int(7, 12) : $n; // Chose length randomly in 7 to 12

        $ddd = array_map(function($a) use ($al){
            $key = random_int(0, 60);
            return $al[$key];
        }, array_fill(0,$len,0));
        return implode('', $ddd);
    }
    
    
    public function agentDepositWithdraw(Request $request)
    {
        $agentId = Auth::user()->id;
        $playerId = $request->playerId;
        $amount = $request->amount;
        $note = $request->note;
        $saveValue = $request->saveValue;
        $totalBalance = 0;

        $activeAgent = DB::table('users')
        ->select('current_balance', 'current_commission')
        ->where('id',  $agentId)->first();

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();


        $user = DB::table('users')->select('id','user_name','current_balance','current_commission')->where('id',  $playerId)->get();
        
        if($amount > 0){
            if($saveValue == 'cashin'){
                if($activeAgent->current_balance >= $amount){
    
                    DB::table('users')
                    ->where('id',  $agentId)->update([
                        'current_balance' => $activeAgent->current_balance - $amount
                    ]);
    
                    $totalBalance = $user[0]->current_balance + $amount;
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $user[0]->id,
                        'transaction_type' => 'cashin',    
                        'amount' => $amount,
                        'current_balance' => $totalBalance,
                        'status' => 1,
                        'note'     => $note,
                        'from' => Auth::user()->user_name,
                        'to' => $user[0]->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
    
                    ]); 
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $agentId,
                        'transaction_type' => 'cashin',    
                        'amount' => $amount,
                        'current_balance' => $activeAgent->current_balance - $amount,
                        'current_commission' => $activeAgent->current_commission,
                        'status' => 2,
                        'note'     => $note,
                        'from' => Auth::user()->user_name,
                        'to' => $user[0]->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
    
                    ]); 
    
                    $description  = $amount .' points loaded to ( ' .  $user[0]->user_name . ' )';
    
    
                    DB::table('users')->where('id', $playerId)
                    ->update([
                        'current_balance' => $totalBalance,
                    ]);
    
                    $activityLog = [
                        'user_id'        => Auth::user()->id,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];
    
                    DB::table('activity_logs')->insert($activityLog);
    
                    $description1  = $amount .' points loaded from ( ' .  Auth::user()->user_name . ' )';
    
                    $activityLog1 = [
                        'user_id'        => $playerId,
                        'description' => $description1,
                        'date_time'   => $todayDate,
                    ];
    
                    DB::table('activity_logs')->insert($activityLog1);
                    
    
                    return response()->json(array('success'=>true));
                }else{
                    return response()->json(array('success'=>false));
                }
                
    
            }elseif($saveValue == 'cashout'){
    
                if($user[0]->current_balance >= $amount){

                    $totalBalance = $user[0]->current_balance - $amount;
    
                    DB::table('users')
                    ->where('id',  $agentId)->update([
                        'current_balance' => $activeAgent->current_balance + $amount
                    ]);
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $user[0]->id,
                        'transaction_type' => 'cashout',    
                        'amount' => $amount,
                        'current_balance' => $totalBalance,
                        'status' => 2,
                        'note'     => $note,
                        'from' => $user[0]->user_name,
                        'to' => Auth::user()->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
                    ]); 
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $agentId,
                        'transaction_type' => 'cashout',    
                        'amount' => $amount,
                        'current_balance' => $activeAgent->current_balance + $amount,
                        'current_commission' => $activeAgent->current_commission,
                        'status' => 1,
                        'note'     => $note,
                        'from' => $user[0]->user_name,
                        'to' => Auth::user()->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
        
                    ]); 
    
                    $description  = $amount .' points withdraw from ( ' .  $user[0]->user_name . ' )';
    
    
                    DB::table('users')->where('id', $playerId)
                    ->update([
                        'current_balance' => $totalBalance,
                    ]);
    
                    $activityLog = [
                        'user_id'        => Auth::user()->id,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];
    
                    DB::table('activity_logs')->insert($activityLog);
    
                    return response()->json(array('success'=>true));
                }else{
                    return response()->json(array('success'=>false));
                }
                
            }elseif($saveValue == 'convert'){
    
                if($user[0]->current_commission >= $amount){
                    $totalBalance = $user[0]->current_commission - $amount;
    
                    DB::table('users')
                    ->where('id',  $agentId)->update([
                        'current_commission' => $activeAgent->current_commission + $amount
                    ]);
    
                    DB::table('users')
                    ->where('id',  $playerId)->update([
                        'current_commission' => $totalBalance
                    ]);
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $user[0]->id,
                        'transaction_type' => 'commission out',    
                        'amount' => $amount,
                        'current_balance' => $user[0]->current_balance,
                        'current_commission' => $totalBalance,
                        'status' => 1,
                        'note'     => $note,
                        'from' => $user[0]->user_name,
                        'to' => Auth::user()->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
                    ]); 
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $agentId,
                        'transaction_type' => 'commission get',    
                        'amount' => $amount,
                        'current_balance' => $activeAgent->current_balance,
                        'current_commission' => $activeAgent->current_commission + $amount,
                        'status' => 1,
                        'note'     => $note,
                        'from' => $user[0]->user_name,
                        'to' => Auth::user()->user_name,
                        'approved_date_time' => $todayDate,
                        'approve_by' => Auth::user()->user_name,
        
                    ]); 
    
                    $description  = $amount .' commission withdraw from ( ' .  $user[0]->user_name . ' )';
    
                    $activityLog = [
                        'user_id'        => Auth::user()->id,
                        'description' => $description,
                        'date_time'   => $todayDate,
                    ];
    
                    DB::table('activity_logs')->insert($activityLog);
    
                    return response()->json(array('success'=>true));
                }else{
                    return response()->json(array('success'=>false));
                }
                
            }else{
                return response()->json(array('success'=>false));
            }
        
        }
    }

    public function getActivePlayers()
    {
        $playerCode = Auth::user()->player_code;

        $agents = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type', 'Player')
        ->where('status', 'Active')
        ->get();

        return view('agents.active_players', compact('agents'));
    }

    public function getDeletedPlayers()
    {
        $playerCode = Auth::user()->player_code;

        $agents = DB::table('users')
        ->where('agent_code', $playerCode)
        ->where('role_type', 'Player')
        ->where('status', '!=', 'Active')
        ->get();

        return view('agents.active_players', compact('agents'));
    }

    public function getPlayerHistory($playerId)
    {

        $selectedUser = DB::table('users')->select('id','user_name','role_type','agent_code')
        ->where('id',  $playerId)->get();

        $allTransactions = collect([]);

        $transactionHistory = DB::table('transactions')
        ->where('user_id', $playerId)
        ->orderBy('id', 'desc')
        ->get();


        foreach($transactionHistory as $history){

            if($history->transaction_type == 'betting' || $history->transaction_type == 'result'){

                if($history->status == 1 || $history->status == 2){
                    $user = DB::table('users')->select('user_name')
                    ->where('id',  $history->user_id)->get();
        
                    $allTransactions->push([
                        'id' => $history->id,
                        'transaction_type' => $history->transaction_type,
                        'status' => $history->status,
                        'amount' => $history->amount,
                        'from' => $history->from,
                        'to' => $history->to,
                        'current_balance' => $history->current_balance,
                        'note' => $history->note,
                        'date' => $history->approved_date_time,
                    ]);
                }
                
            }

        }
       
        $allTransactions->all();

        return view('agents.player_history', compact('selectedUser','allTransactions'));
    }
}
