<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;


class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isOperators']);
    }

    public function index()
    {
        $data['events'] = Event::orderBy('id', 'desc')->get();
        return view('operators.events.index',$data);
    }

    // public function create()
    // {
    //     return view('operators.events.create');
    // }

    // save 
    public function store(Request $request)
    {
        $event = Event::updateOrCreate([
            'id' => $request->event_id
        ],
        [
            'event_name'     => $request->event_name,
            'fight_date_time' => $request->fight_date_time,
            'location' => $request->location,
            'video_code' => $request->video_code,
            'palasada' => $request->palasada,
            'user_id' => Auth::user()->id,
            'status' => $request->status,
        ]);  
        
        return response()->json($event);
    }

    public function edit($id)
    {
        $event = Event::find($id);
        return response()->json($event);
    }
}
