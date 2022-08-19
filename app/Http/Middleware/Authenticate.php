<?php

namespace App\Http\Middleware;

use App\Services\Utility;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Cookie;

class Authenticate extends Middleware
{

    protected function redirectTo($request)
    {
        //response()->withCookie( Cookie::forget(Utility::$TOKEN) );
    }
    public function handle($request, Closure $next, ...$guards)
    {
        $request->headers->set('accept', 'application/json', true);
        if ($jwt = $request->cookie(Utility::$TOKEN)) {
            $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }
        $this->authenticate($request, $guards);

        return $next($request);
    }
}
