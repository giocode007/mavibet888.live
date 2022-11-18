<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Brian2694\Toastr\Facades\Toastr;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'locked',
            'unlock'
        ]);
    }

    public function login()
    {

    return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $username    = $request->user_name;
        $password = $request->password;
        
        $dt         = Carbon::now();
        $todayDate  = $dt->toDayDateTimeString();

        $activityLog = [
            'user_name'        => $username,
            'description' => 'has log in',
            'date_time'   => $todayDate,
        ];
        
        if (Auth::attempt(['user_name'=>$username,'password'=>$password,'status'=>'Active'])) {
            DB::table('activity_logs')->insert($activityLog);
            Toastr::success('Login successfully :)','Success');
            return redirect()->intended('home');
        }
        else{
            Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
            return redirect('login');
        }

    }

    public function logout()
    {
        $user = Auth::User();
        Session::put('user', $user);
        $user=Session::get('user');

        $username       = $user->user_name;
        $dt         = Carbon::now();
        $todayDate  = $dt->toDayDateTimeString();

        $activityLog = [

            'user_name'        => $username,
            'description' => 'has logged out',
            'date_time'   => $todayDate,
        ];
        
        DB::table('activity_logs')->insert($activityLog);
        Auth::logout();
        Toastr::success('Logout successfully :)','Success');
        return redirect('login');
    }

}
