<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','isAgents']);
    }


    public function dashboard()
    {
        $staff = DB::table('staff')->count();
        $users = DB::table('users')->count();
        $user_activity_logs = DB::table('user_activity_logs')->count();
        $activity_logs = DB::table('activity_logs')->count();
        return view('agents.dashboard',compact('staff','users','user_activity_logs','activity_logs'));
    }

    public function summaryReport()
    {
        $staff = DB::table('staff')->count();
        $users = DB::table('users')->count();
        $user_activity_logs = DB::table('user_activity_logs')->count();
        $activity_logs = DB::table('activity_logs')->count();
        return view('agents.summary_report',compact('staff','users','user_activity_logs','activity_logs'));
    }

    public function loadLogs()
    {
        $staff = DB::table('staff')->count();
        $users = DB::table('users')->count();
        $user_activity_logs = DB::table('user_activity_logs')->count();
        $activity_logs = DB::table('activity_logs')->count();
        return view('agents.load_logs',compact('staff','users','user_activity_logs','activity_logs'));
    }

    public function commissionLogs()
    {
        $staff = DB::table('staff')->count();
        $users = DB::table('users')->count();
        $user_activity_logs = DB::table('user_activity_logs')->count();
        $activity_logs = DB::table('activity_logs')->count();
        return view('agents.commission_logs',compact('staff','users','user_activity_logs','activity_logs'));
    }

    public function commissionWithdrawal()
    {
        $staff = DB::table('staff')->count();
        $users = DB::table('users')->count();
        $user_activity_logs = DB::table('user_activity_logs')->count();
        $activity_logs = DB::table('activity_logs')->count();
        return view('agents.commission_withdrawal',compact('staff','users','user_activity_logs','activity_logs'));
    }
}
