<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Lawyer
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
        if(Auth::check()) {
            if(Auth::user()->flag == 4 || Auth::user()->flag == 5) {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
