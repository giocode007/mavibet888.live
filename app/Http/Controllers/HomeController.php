<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    

    public function backPage()
    {
        if (Auth::user()->role_type=='Operator' || Auth::user()->role_type=='Declarator')
        {
            return redirect()->intended('/admin');
        }
        else if (Auth::user()->role_type == 'Admin' ||
                    Auth::user()->role_type == 'Sub_Operator' || 
                        Auth::user()->role_type == 'Master_Agent' ||
                            Auth::user()->role_type == 'Gold_Agent')
        {
            return redirect()->intended('/dashboard');
        }
        else{
            return redirect()->intended('/home');
        }
    }
}
