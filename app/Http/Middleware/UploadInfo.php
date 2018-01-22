<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class UploadInfo
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
        if(Auth::user()->flag == 0) {
            return $next($request);
        } else if (Auth::user()->flag == 2) {
            return response(view('kyc.conformation'));
        } else if (Auth::user()->flag == 3) {
            return response(view('disabled'));
        }

        return redirect('/');
    }
}
