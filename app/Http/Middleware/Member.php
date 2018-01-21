<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Member
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        if(Auth::user()->flag == 1 || Auth::user()->flag == 2)
        {
            return $next($request);

        } else if (Auth::user()->flag == 0) {

            return redirect('/kyc');

        } else if (Auth::user()->flag == 3) {

            return response(view('disabled'));

        } else if (Auth::user()->flag == 4) {

            return redirect('/verification');

        }  else if (Auth::user()->flag == 5) {
            return redirect('/duo');
            //return redirect('/admin');
        }

        return redirect('/');
    }
}
