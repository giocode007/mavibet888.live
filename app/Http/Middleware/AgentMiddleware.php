<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check()){

            if(Auth::user()->role_type == 'Admin' ||
                    Auth::user()->role_type == 'Sub_Operator' || 
                        Auth::user()->role_type == 'Master_Agent' ||
                            Auth::user()->role_type == 'Gold_Agent'){
                                return $next($request);
                            }
            else{
                return redirect('errors.404');
            }

        }else{
            return redirect('/login');
        }
        return $next($request);
    }
}
