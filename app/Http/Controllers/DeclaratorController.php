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



class DeclaratorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isDeclarators']);
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
        return redirect()->intended('/declarator');
    }

}
