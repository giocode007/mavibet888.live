<?php

namespace App\Http\Controllers\Auth;

use Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register()
    {
        return view('auth.register');
    }
    public function storeUser(Request $request)
    {   
        if(DB::table('users')->where('agent_code', $request->agent_code)->exists()){
        $request->validate([
            'agent_code'      => 'required',
            'user_name'      => 'required|min:6|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'last_name'      => 'required',
            'first_name'      => 'required',
            'phone_number' => 'required|numeric',
            'role_name' => 'required',
            'status' => 'required'
        ]);

        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'role_name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => ['required', 'confirmed', Password::min(8)
        //             ->mixedCase()
        //             ->letters()
        //             ->numbers()
        //             ->symbols()
        //             ->uncompromised(),
        //     'password_confirmation' => 'required',
        //     ],
        // ]);
        
        User::create([
            'agent_code'      => $request->agent_code,
            'user_name'      => $request->user_name,
            'password'  => Hash::make($request->password),
            'last_name'      => $request->last_name,
            'first_name'      => $request->first_name,
            'phone_number'      => $request->phone_number,
            'avatar'    => $request->image,
            'role_name' => $request->role_name,
            'status' => $request->status,
        ]);

        Toastr::success('Create new account successfully :)','Success');
        return redirect('login');
        }

        else{
            return back()->withErrors(['agent_code' => 'Code not exist'])->withInput();
        }
    }
}
