<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;



class OperatorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isOperators']);
    }

    public function index()
    {
        $events = DB::table('events')->get();
        foreach($events as $event){
            if($event->status == '1'){
                return view('operators.home',compact('event'));
            }
        }

        return view('operators.home');
        
    }

    public function getAgents()
    {
        $agents;
        if(Auth::user()->role_type == 'Operator'){
            $agents = DB::table('users')
            ->orWhere('role_type', 'Operator')
            ->orWhere('role_type', 'Sub_Admin')
            ->orWhere('role_type', 'Sub_Operator')
            ->orWhere('role_type', 'Master_Agent')
            ->orWhere('role_type', 'Gold_Agent')
            ->orderBy('id', 'desc')
            ->get();
        }else{
            $agents = DB::table('users')
            ->orWhere('role_type', 'Sub_Admin')
            ->orWhere('role_type', 'Sub_Operator')
            ->orWhere('role_type', 'Master_Agent')
            ->orWhere('role_type', 'Gold_Agent')
            ->orderBy('id', 'desc')
            ->get();
        }
        
        
        return view('operators.agents', compact('agents'));
    }

    public function allAgents()
    {
        $agents = DB::table('users')
        ->orWhere('role_type', 'Sub_Admin')
        ->orWhere('role_type', 'Sub_Operator')
        ->orWhere('role_type', 'Master_Agent')
        ->orWhere('role_type', 'Gold_Agent')
        ->orderBy('current_balance', 'desc')
        ->get();

        return view('operators.all_agents', compact('agents'));
    }

    public function allPlayers()
    {
        $players = DB::table('users')
        ->where('role_type', 'Player')
        ->orderBy('current_balance', 'desc')
        ->get();
        
        return view('operators.all_players', compact('players'));
    }

    public function getPlayers($playerCode)
    {
        $selectedUser = DB::table('users')
        ->select('id','user_name','player_code')->where('player_code',  $playerCode)->get();

        $players = DB::table('users')
        ->where('agent_code', $playerCode)
        ->orderBy('current_balance', 'desc')
        ->get();

        return view('operators.players', compact('players', 'selectedUser'));
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

        $playerRole = $request->playerRole;
        $playerStatus = $request->playerStatus;
        $agentCode = $request->agentCode;
        $avatar = '';

        if($playerInfo[0]->role_type != $playerRole){
            
            if($playerRole == 'Declarator' || $playerRole == 'Loader'){
                $avatar = 'operator_defaults.jpg';
                
                DB::table('users')->where('id', $playerId)
                ->update([
                    'role_type' => $playerRole,
                    'avatar' => $avatar,
                ]);
    
            }else{
                $avatar = 'admin_defaults.jpg';

                if($playerInfo[0]->role_type == 'Player'){
                    $code = Str::upper($this->generateRandomString(6));

                    DB::table('users')->where('id', $playerId)
                    ->update([
                        'role_type' => $playerRole,
                        'player_code' => $code,
                        'avatar' => $avatar,
                    ]);
                }else{
                    DB::table('users')->where('id', $playerId)
                    ->update([
                        'role_type' => $playerRole,
                        'avatar' => $avatar,
                    ]);
                }
            }
        }else{
            $currComm = $request->currComm / 100;

            $request->validate([
                'agentCode'      => 'exists:users,player_code',
            ]);

            DB::table('users')->where('id', $playerId)
            ->update([
                'commission_percent' => $currComm,
                'status' => $playerStatus,
                'agent_code' => $agentCode,
            ]);
        }

        

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();


        $description  = 'Change player: (' . $playerInfo[0]->user_name . 
        ') Role: ' . $playerInfo[0]->role_type . ' to ' . $playerRole .
        ' Current Commission: ' . $playerInfo[0]->current_commission . ' to ' . $request->currComm . 
        ' Status: ' . $playerInfo[0]->status . ' to ' . $playerStatus . 
        ' Agent: ' . $playerInfo[0]->agent_code . ' to ' . $agentCode;
        
        
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);
        
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
    
    public function getLogs($userId)
    {
        $logs = DB::table('activity_logs')
        ->where('user_id', $userId)
        ->orderBy('id', 'desc')
        ->get();

        $user = DB::table('users')
        ->select('role_type', 'agent_code','user_name','id')
        ->where('id', $userId)
        ->first();
        
        return view('operators.logs', compact('user','logs'));
    }

    public function getProfile($id)
    {
        $data = DB::table('users')->where('id', $id)->get();

        return view('operators.change_password', compact('data'));
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
        return redirect()->intended('/admin');
    }

    public function getHistory($agentId)
    {
        $selectedUser = DB::table('users')->select('id','user_name','role_type')->where('id',  $agentId)->get();

        $allTransactions = collect([]);

        if(Auth::user()->role_type != 'Operator'){
            $transactionHistory = DB::table('transactions')
            ->where('user_id', $agentId)
            ->orderBy('id', 'desc')->get();
        }else{
            $transactionHistory = DB::table('transactions')
            ->where('user_id', $agentId)
            ->orWhere('approve_by', $selectedUser[0]->user_name)
            ->orderBy('id', 'desc')->get();
        }
        

        foreach($transactionHistory as $history){

            if($history->transaction_type != 'commission' && $history->transaction_type != 'commission out'
            && $history->transaction_type != 'commission get' && $history->transaction_type != 'betting'
            && $history->transaction_type != 'result'){
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
        return view('operators.history', compact('selectedUser','allTransactions'));
    }

    public function agentDepositWithdraw(Request $request)
    {

        $playerId = $request->playerId;
        $amount = $request->amount;
        $note = $request->note;
        $saveValue = $request->saveValue;
        $totalBalance = 0;

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        $user = DB::table('users')->select('id','user_name','current_balance','current_commission')->where('id',  $playerId)->get();

        if($amount > 0){
            if($saveValue == 'deposit'){
            
                if(Auth::user()->role_type == 'Operator'){
                    $totalBalance = $user[0]->current_balance + $amount;
    
                    $transaction = Transactions::updateOrCreate([
                        'id' => $request->transaction_id
                    ],
                    [
                        'user_id' => $user[0]->id,
                        'transaction_type' => 'deposit',    
                        'amount' => $amount,
                        'current_balance' => $totalBalance,
                        'status' => 1,
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
                }elseif(Auth::user()->role_type == 'Loader' && Auth::user()->current_balance >= $amount){
                    $totalBalance = $user[0]->current_balance + $amount;
    
                    $currLoader = DB::table('users')->select('current_balance')
                    ->where('id',  Auth::user()->id)->first();
    
                    DB::table('users')->where('id', Auth::user()->id)
                    ->update([
                        'current_balance' => $currLoader->current_balance - $amount,
                    ]);
    
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
                }
    
            }else if($saveValue == 'withdraw'){
    
                if(Auth::user()->role_type == 'Operator'){
                    if($user[0]->current_balance >= $amount){
                        $totalBalance = $user[0]->current_balance - $amount;
        
                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $user[0]->id,
                            'transaction_type' => 'withdraw',    
                            'amount' => $amount,
                            'current_balance' => $totalBalance,
                            'status' => 2,
                            'note'     => $note,
                            'from' => Auth::user()->user_name,
                            'to' => $user[0]->user_name,
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
                }
                
               
            }else{
    
                if(Auth::user()->role_type == 'Operator'){
                    if($user[0]->current_commission >= $amount){
                        $totalCommission = $user[0]->current_commission - $amount;
                        $totalBalance = $user[0]->current_balance + $amount;
        
                        $transaction = Transactions::updateOrCreate([
                            'id' => $request->transaction_id
                        ],
                        [
                            'user_id' => $user[0]->id,
                            'transaction_type' => 'convert',    
                            'amount' => $amount,
                            'current_commission' => $totalCommission,
                            'current_balance' => $totalBalance,
                            'status' => 1,
                            'note'     => $note,
                            'from' => Auth::user()->user_name,
                            'to' => $user[0]->user_name,
                            'approved_date_time' => $todayDate,
                            'approve_by' => Auth::user()->user_name,
                        ]); 
        
                        $description  = $amount .' commission convert from ( ' .  $user[0]->user_name . ' )';
        
                        DB::table('users')->where('id', $playerId)
                        ->update([
                            'current_commission' => $totalCommission,
                            'current_balance' => $totalBalance,
                        ]);
        
                        $activityLog = [
                            'user_id'        => Auth::user()->id,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
        
                        DB::table('activity_logs')->insert($activityLog);
        
                        $description  = $amount .' commission converted to points';
        
                        $activityLog = [
                            'user_id'        => $playerId,
                            'description' => $description,
                            'date_time'   => $todayDate,
                        ];
        
                        DB::table('activity_logs')->insert($activityLog);
        
                        return response()->json(array('success'=>true));
                    }else{
                        return response()->json(array('success'=>false));
                    }
                }
                
            }
        }
        
        
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

            if($history->transaction_type == 'cashin' || $history->transaction_type == 'cashout' 
            || $history->transaction_type == 'betting' || $history->transaction_type == 'result'){

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

        return view('operators.player_history', compact('selectedUser','allTransactions'));
    }

    public function getCommission($userId)
    {
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
        
        return view('operators.commission', compact('commissionHistory', 'selectedUser'));
    }

    public function getAudit()
    {

        $auditHistory = collect([]);

        $auditHistory->all();
        
        return view('operators.audit', compact('auditHistory'));
    }

    public function computeProfit(Request $request)
    {

        $totalDeposit = 0;
        $totalWithdraw = 0;
        $totalCurrentCommission = 0;
        $totalCurrentBalance = 0;

        if($request->from_date_time != null && $request->to_date_time != null){
            $trans_from = Carbon::createFromFormat('Y-m-d H:i', $request->from_date_time)->toDateTimeString(); 
            $trans_to = Carbon::createFromFormat('Y-m-d H:i', $request->to_date_time)->toDateTimeString(); 
    
            
            $usersId = collect([]);
            $lastTransactions = collect([]);
            $usersRemoveDupicate = [];
    
            $filter_transactions =  DB::table('transactions')
            ->select('id', 'user_id', 'status', 'transaction_type', 'amount')
            ->whereBetween('created_at', [$trans_from, $trans_to])
            ->where('transaction_type', 'deposit')
            ->orWhere('transaction_type', 'withdraw')
            ->orderBy('id', 'desc')
            ->get();

    
            foreach($filter_transactions as $history){
                    $user = DB::table('users')->select('role_type')
                    ->where('id',  $history->user_id)->first();

                    if($history->transaction_type == 'deposit'){
                        $totalDeposit += $history->amount;
                    }
    
                    if($history->transaction_type == 'withdraw'){
                        $totalWithdraw += $history->amount;
                    }
    
            }


            $currentUsersAmount =  DB::table('users')
            ->select('id', 'user_name', 'role_type', 'current_balance', 'current_commission')
            ->where('role_type', '!=', 'Operator')
            ->where('role_type', '!=', 'Declarator')
            ->get();
    
            foreach($currentUsersAmount as $user){
                $totalCurrentBalance += $user->current_balance;
                $totalCurrentCommission += $user->current_commission;

                if($user->current_balance != 0 || $user->current_commission != 0 ){
                    $lastTransactions->push([
                        'id' => $user->id,
                        'username' => $user->user_name,
                        'current_balance' => $user->current_balance,
                        'current_commission' => $user->current_commission,
                    ]);
                }
                    
            }
            $lastTransactions->all();

            $totalGross = $totalDeposit - ( $totalWithdraw + $totalCurrentBalance + $totalCurrentCommission ) ;
    
            return view('operators.compute', compact('totalDeposit','totalWithdraw','totalCurrentBalance','totalCurrentCommission','lastTransactions', 'totalGross'));
        }else{
            $auditHistory = collect([]);
        
            $auditHistory->all();
            return view('operators.audit', compact('auditHistory'));
        }

    }

    public function profitHistory(Request $request)
    {

        if($request->from_date_time != null && $request->to_date_time != null){
            $trans_from = Carbon::createFromFormat('Y-m-d H:i', $request->from_date_time)->toDateTimeString(); 
            $trans_to = Carbon::createFromFormat('Y-m-d H:i', $request->to_date_time)->toDateTimeString(); 
            
            $usersId = collect([]);
            $lastTransactions = collect([]);
            $usersRemoveDupicate = [];
    
            $filter_transactions =  DB::table('transactions')
            ->select('id', 'user_id', 'status', 'transaction_type', 'amount')
            ->whereBetween('created_at', [$trans_from, $trans_to])
            ->orderBy('id', 'desc')
            ->get();
    
            foreach($filter_transactions as $history){
                if($history->status == 1 || $history->status == 2){
                    $user = DB::table('users')->select('role_type')
                    ->where('id',  $history->user_id)->first();

                    if($user->role_type != 'Operator' && $user->role_type != 'Declarator'){
                        if(!in_array($history->user_id, $usersRemoveDupicate)){
                            array_push($usersRemoveDupicate, $history->user_id);

                            $user = DB::table('transactions')
                            ->select('current_balance', 'current_commission')
                            ->where('user_id',  $history->user_id)
                            ->whereBetween('created_at', [$trans_from, $trans_to])
                            ->orderBy('id', 'desc')
                            ->first();

                            $activeUser = DB::table('users')
                            ->select('user_name')
                            ->where('id',  $history->user_id)
                            ->first();

                            $lastTransactions->push([
                                'id' => $history->id,
                                'user_id' => $history->user_id,
                                'username' => $activeUser->user_name,
                                'current_balance' => $user->current_balance,
                                'current_commission' => $user->current_commission,
                            ]);
                        }
                    }
                    
                }
                    
            }

            $lastTransactions->all();
    
            return view('operators.compute', compact('lastTransactions'));
        }else{
            $auditHistory = collect([]);
        
            $auditHistory->all();
            return view('operators.audit', compact('auditHistory'));
        }

    }

    public function forgetPassword(Request $request)
    {
        $playerId = $request->playerId;
        $password = $request->password;

        $playerInfo = DB::table('users')
        ->where('id', $playerId)
        ->get();

        DB::table('users')->where('id', $playerId)
        ->update([
            'password' => Hash::make($request->password)
        ]);
        

        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();


        $description  = 'Change player: (' . $playerInfo[0]->user_name . 
        ') Password to : ' . $request->password;
        
        
        $activityLog = [
            'user_id'        => Auth::user()->id,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);
        
    }
   
    public function removePoints(Request $request){
        $transactionId = $request->transactionId;
        $changeBet;

        $activeTransaction = DB::table('transactions')->where('id', $transactionId)->first();
        $activeUser = DB::table('users')->select('current_balance')->where('id', $activeTransaction->user_id)->first();
        $activeBetting = DB::table('bettings')->select('bet_type', 'result','amount')->where('id', $activeTransaction->betting_id)->first();
        $allTransactions = DB::table('transactions')
        ->where('id', '>', $activeTransaction->id)
        ->where('user_id', $activeTransaction->user_id)
        ->get();

        if($activeBetting->bet_type == 'meron'){
            $changeBet = 'wala';
        }else{
            $changeBet = 'meron';
        }

        DB::table('bettings')->where('id', $activeTransaction->betting_id)
        ->update([
            'bet_type' => $changeBet
        ]);

        DB::table('transactions')->where('betting_id', $activeTransaction->betting_id)->where('transaction_type', 'betting')
        ->update([
            'note' => 'Bet on ' . $changeBet
        ]);

        $currPoints = $activeTransaction->current_balance - $activeTransaction->amount;
        $currAmount = $activeBetting->amount;
        $currBal = 0;

        DB::table('transactions')->where('id', $transactionId)
        ->update([
            'amount' => 0,
            'current_balance' => $currPoints,
            'status' => 2,
            'note' => 'LOSE: result: ' . $activeBetting->result . ' = Bet: ' . $changeBet
        ]);

        DB::table('users')->where('id', $activeTransaction->user_id)
        ->update([
            'current_balance' => $activeUser->current_balance - $activeTransaction->amount,
        ]);

        foreach($allTransactions as $history){
            $newAmount = $history->amount;

            if($history->status == 1){
                $currBal =  $currPoints + $newAmount;
                $currAmount = $newAmount;
                $currPoints = $currBal;

            }elseif($history->status == 2){
                $currBal =  $currPoints - $newAmount;
                $currAmount = $newAmount;
                $currPoints = $currBal;
            }

            DB::table('transactions')->where('id', $history->id)
            ->update([
                'current_balance' => $currBal,
            ]);

        }

    }

    public function export($action) 
    {

        $exportedList = collect([]);
       
        if($action == 'exportLogs'){
            $logs =  DB::table('activity_logs')->get();

            foreach($logs as $data){
                $user = DB::table('users')
                    ->select('user_name')
                    ->where('id',  $data->user_id)
                    ->first();

                    $exportedList->push([
                        'id' => $data->id,
                        'user_name' => $user->user_name,
                        'description' => $data->description,
                        'date' => $data->date_time,
                    ]);
                
            }

            $exportedList->all();

            $export = new InvoicesExport([$exportedList]);
            return Excel::download($export, 'activity_logs.xlsx');
        }else if($action == 'exportEvents'){
            $events =  DB::table('events')->get();

            foreach($events as $data){
                $user = DB::table('users')
                    ->select('user_name')
                    ->where('id',  $data->user_id)
                    ->first();

                    $exportedList->push([
                        'id' => $data->id,
                        'user_name' => $user->user_name,
                        'event_name' => $data->event_name,
                        'date' => $data->fight_date_time,
                        'location' => $data->location,
                        'video_source' => $data->video_code,
                        'plasada' => $data->palasada,
                    ]);
                
            }

            $exportedList->all();

            $export = new InvoicesExport([$exportedList]);
            return Excel::download($export, 'events.xlsx');
        }else if($action == 'exportFights'){
            $fights =  DB::table('fights')->get();

            foreach($fights as $data){
                $event = DB::table('events')
                    ->select('event_name')
                    ->where('id',  $data->event_id)
                    ->first();

                    $exportedList->push([
                        'id' => $data->id,
                        'event_name' => $event->event_name,
                        'fight_number' => $data->fight_number,
                        'result' => $data->result,
                        'payout_meron' => $data->payoutMeron,
                        'payout_wala' => $data->payoutWala,
                        'declared_by' => $data->declared_by,
                        'date' => $data->created_at,
                    ]);
                
            }

            $exportedList->all();

            $export = new InvoicesExport([$exportedList]);
            return Excel::download($export, 'fights.xlsx');
        }else if($action == 'exportHistory'){
            $transaction =  DB::table('transactions')->get();

            foreach($transaction as $data){
                $user = DB::table('users')
                    ->select('user_name', 'role_type')
                    ->where('id',  $data->user_id)
                    ->first();

                    if($user->role_type != 'Operator' && $user->role_type != 'Declarator'){
                        $exportedList->push([
                            'id' => $data->id,
                            'user_name' => $user->user_name,
                            'transaction_type' => $data->transaction_type,
                            'amount' => $data->amount,
                            'current_balance' => $data->current_balance,
                            'current_commission' => $data->current_commission,
                            'note' => $data->note,
                            'from' => $data->from,
                            'to' => $data->to,
                            'date' => $data->approved_date_time,
                            'approved_by' => $data->approve_by,
                        ]);
                    }
                    
            }

            $exportedList->all();

            $export = new InvoicesExport([$exportedList]);
            return Excel::download($export, 'history.xlsx');
        }

    }
}
