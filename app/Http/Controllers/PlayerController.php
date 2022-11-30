<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
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
    }




    // view change password
    public function changePasswordView()
    {
        return view('players.change_password');
    }
    
    // change password in db
    public function changePasswordDB(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'password'  => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
   
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->password)]);
        Toastr::success('User change successfully :)','Success');
        return redirect()->route('home');
    }
}
