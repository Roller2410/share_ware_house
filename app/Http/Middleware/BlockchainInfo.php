<?php

namespace App\Http\Middleware;

use Closure;

class BlockchainInfo extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->middleware(function ($request, $next) {

            $this->ICOPhase = $this->currentICOPhase();
            $this->basic = $this->basicWallet();

            return $next($request);

        });
    }
}
