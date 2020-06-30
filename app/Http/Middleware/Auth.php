<?php

namespace App\Http\Middleware;

use App\Models\Master;
use Closure;

class Auth
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
        if (session()->has('access')){
            return $next($request);

        }

        else{
            return redirect()->route('SignIn');
        }

    }
}
