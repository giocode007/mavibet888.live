<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isPlayers']);
    }
    
    //Home
    public function index()
    {
        $events = DB::table('events')->get();
        foreach($events as $event){
            if($event->status == '1'){
                return view('players.home',compact('event'));
            }
        }

        return view('players.home');
    }




    // view change password
    public function getProfile($id)
    {
        $data = DB::table('users')->where('id', $id)->get();

        return view('players.change_password', compact('data'));
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
            'status'        => 1,
            'description' => $description,
            'date_time'   => $todayDate,
        ];

        DB::table('activity_logs')->insert($activityLog);

        Toastr::success('User change successfully :)','Success');
        return redirect()->intended('/home');
    }

    public function getPlayerBettingHistory()
    {
        $playerId = Auth::user()->id;
        
        $selectedUser = DB::table('users')->select('id','user_name','role_type','agent_code')
        ->where('id',  $playerId)->get();

        $allTransactions = collect([]);

        $transactionHistory = DB::table('transactions')
        ->where('user_id', $playerId)
        ->orWhere('to', $selectedUser[0]->user_name)
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

        return view('players.betting_history', compact('selectedUser','allTransactions'));
    }

    public function getPlayerTransactionHistory()
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

            if(($history->transaction_type == 'deposit' || $history->transaction_type == 'withdraw')
            || ($history->transaction_type == 'cashin' || $history->transaction_type == 'cashout')){

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

        return view('players.transaction_history', compact('selectedUser','allTransactions'));
    }
}
