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
    protected $redirectTo = '/';

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

    public function login(Request $request)
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
        
        
        if (Auth::attempt(['user_name'=>$username,'password'=>$password])) {
            if(Auth::user()->status == 'Active'){
                $dt         = Carbon::now('Asia/Manila');
                $todayDate  = $dt->toDayDateTimeString();
        
                $activityLog = [
                    'user_id'        => Auth::user()->id,
                    'status'        => 1,
                    'description' => 'has log in IP : ' . $request->ip(),
                    'date_time'   => $todayDate,
                ];
    
                DB::table('activity_logs')->insert($activityLog);
                Toastr::success('Login successfully :)','Success');
    
                if (Auth::user()->role_type=='Operator' || Auth::user()->role_type == 'Loader')
                    {
                        return redirect()->intended('/admin');
                    }
                else if (Auth::user()->role_type=='Declarator')
                {
                    return redirect()->intended('/declarator');
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
            }else{
                $user = Auth::User();
                Session::put('user', $user);
                $user=Session::get('user');
                Auth::logout();
                Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
                return redirect('login');
            }
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

        $userId       = $user->id;
        $dt         = Carbon::now('Asia/Manila');
        $todayDate  = $dt->toDayDateTimeString();

        $activityLog = [

            'user_id'        => $userId,
            'status'        => 1,
            'description' => 'has logged out',
            'date_time'   => $todayDate,
        ];
        
        DB::table('activity_logs')->insert($activityLog);
        Auth::logout();
        Toastr::success('Logout successfully :)','Success');
        return redirect('login');
    }

}
