<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class LoaderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isLoaders']);
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
        $agents = DB::table('users')
        ->orWhere('role_type', 'Operator')
        ->orWhere('role_type', 'Sub_Operator')
        ->orWhere('role_type', 'Master_Agent')
        ->orWhere('role_type', 'Gold_Agent')
        ->orderBy('id', 'desc')
        ->get();
        
        return view('operators.agents', compact('agents'));
    }

    public function allAgents()
    {
        $agents = DB::table('users')
        ->where('role_type', 'Sub_Operator')
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
        $selectedUser = DB::table('users')->select('id','user_name','player_code')->where('player_code',  $playerCode)->get();

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
        $currComm = $request->currComm;
        $playerStatus = $request->playerStatus;
        $agentCode = $request->agentCode;
        $avatar = '';

        if($playerInfo[0]->role_type != $playerRole){
            $code = Str::upper($this->generateRandomString(6));

            if($playerRole == 'Declarator' || $playerRole == 'Loader'){
                $avatar = 'operator_defaults.jpg';
                
                DB::table('users')->where('id', $playerId)
                ->update([
                    'role_type' => $playerRole,
                    'commission_percent' => $currComm,
                    'status' => $playerStatus,
                    'player_code' => null,
                    'avatar' => $avatar,
                ]);
    
            }else{
                $avatar = 'admin_defaults.jpg';
    
                DB::table('users')->where('id', $playerId)
                ->update([
                    'role_type' => $playerRole,
                    'commission_percent' => $currComm,
                    'player_code' => $code,
                    'status' => $playerStatus,
                    'avatar' => $avatar,
                ]);
            }
        }else{
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
        ' Current Commission: ' . $playerInfo[0]->current_commission . ' to ' . $currComm . 
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
        ->select('role_type', 'agent_code')
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

        $transactionHistory = DB::table('transactions')->where('user_id', $agentId)
        ->orWhere('approve_by', $selectedUser[0]->user_name)->orderBy('id', 'desc')->get();

        foreach($transactionHistory as $history){

            if($history->transaction_type == 'deposit' || $history->transaction_type == 'withdraw'
            || $history->transaction_type == 'convert'){
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

        if($saveValue == 'deposit'){
            
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

        }else if($saveValue == 'withdraw'){

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
           
        }else{
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
                    'status' => 3,
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

    public function getPlayerHistory($playerId)
    {

        $selectedUser = DB::table('users')->select('id','user_name','role_type','agent_code')
        ->where('id',  $playerId)->get();

        $allTransactions = collect([]);

        $transactionHistory = DB::table('transactions')
        ->where('user_id', $playerId)
        ->orWhere('to', $selectedUser[0]->user_name)
        ->orderBy('id', 'desc')
        ->get();


        foreach($transactionHistory as $history){

            if($history->transaction_type == 'deposit' || $history->transaction_type == 'withdraw' 
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
        ->orWhere('to', $selectedUser->user_name)
        ->orderBy('id', 'desc')
        ->get();

        foreach($transactionHistory as $history){

            if($history->transaction_type == 'commission' || $history->transaction_type == 'convert'){

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

        $transactionHistory = DB::table('transactions')
        ->orderBy('id', 'desc')
        ->get();

        foreach($transactionHistory as $history){
            if($history->status == 1 || $history->status == 2){
                $user = DB::table('users')->select('user_name')
                ->where('id',  $history->user_id)->first();
    
                $auditHistory->push([
                    'id' => $history->id,
                    'username' => $user->user_name,
                    'transaction_type' => $history->transaction_type,
                    'amount' => $history->amount,
                    'current_balance' => $history->current_balance,
                    'current_commission' => $history->current_commission,
                    'status' => $history->status,
                    'note' => $history->note,
                    'from' => $history->from,
                    'to' => $history->to,
                    'approved_date_time' => $history->approved_date_time,
                ]);
            }
        }
       
        $auditHistory->all();
        
        return view('operators.audit', compact('auditHistory'));
    }

    public function computeProfit(Request $request)
    {

        $totalDeposit = 0;
        $totalWithdraw = 0;
        $totalCurrentCommission = 0;
        $totalCurrentBalance = 0;


        $trans_from = Carbon::createFromFormat('Y-m-d H:i', $request->from_date_time)->toDateTimeString(); 
        $trans_to = Carbon::createFromFormat('Y-m-d H:i', $request->to_date_time)->toDateTimeString(); 

        
        $transaction = collect([]);
        $usersId = collect([]);
        $lastTransactions = collect([]);
        //2022-12-29 20:21:29

        $filter_transactions =  DB::table('transactions')
        ->whereBetween('created_at', [$trans_from, $trans_to])
        ->orderBy('id', 'desc')
        ->get();


        foreach($filter_transactions as $history){
            if($history->status == 1 || $history->status == 2){
                $user = DB::table('users')->select('user_name', 'role_type')
                ->where('id',  $history->user_id)->first();

                if($history->transaction_type == 'deposit'){
                    $totalDeposit += $history->amount;
                }

                if($history->transaction_type == 'withdraw'){
                    $totalWithdraw += $history->amount;
                }


                $transaction->push([
                    'id' => $history->id,
                    'username' => $user->user_name,
                    'transaction_type' => $history->transaction_type,
                    'amount' => $history->amount,
                    'current_balance' => $history->current_balance,
                    'current_commission' => $history->current_commission,
                    'status' => $history->status,
                    'note' => $history->note,
                    'from' => $history->from,
                    'to' => $history->to,
                    'approved_date_time' => $history->approved_date_time,
                ]);

                $usersId->push([
                    'id' => $history->user_id,
                    'role_type' => $user->role_type,
                ]);
            }
                
        }

        $transaction->all();
        $usersId->all();

        $usersRemoveDupicate = [];

        for($i=0; $i<count($usersId); $i++){

            if($usersId[$i]['role_type'] != 'Operator'){
                if(!in_array($usersId[$i], $usersRemoveDupicate)){
                    array_push($usersRemoveDupicate, $usersId[$i]);
                }
            }
        }



        for($i=0; $i<count($usersRemoveDupicate); $i++){

            $user = DB::table('transactions')
            ->where('user_id',  $usersRemoveDupicate[$i]['id'])
            ->whereBetween('created_at', [$trans_from, $trans_to])
            ->orderBy('id', 'desc')
            ->first();

            $totalCurrentBalance += $user->current_balance;
            $totalCurrentCommission += $user->current_commission;

            $lastTransactions->push([
                'id' => $user->id,
                'user_id' => $user->user_id,
                'transaction_type' => $user->transaction_type,
                'amount' => $user->amount,
                'current_balance' => $user->current_balance,
                'current_commission' => $user->current_commission,
                'status' => $user->status,
                'note' => $user->note,
                'from' => $user->from,
                'to' => $user->to,
                'approved_date_time' => $user->approved_date_time,
            ]);
        }

        $lastTransactions->all();

        $totalGross = $totalDeposit - ( $totalWithdraw + $totalCurrentBalance + $totalCurrentCommission ) ;

        return view('operators.compute', compact('transaction', 'totalGross'));
    }
   
}
