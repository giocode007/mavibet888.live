<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
