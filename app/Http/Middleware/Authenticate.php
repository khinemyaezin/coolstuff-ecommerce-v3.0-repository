<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    protected function redirectTo($request)
    {
        //response()->withCookie( Cookie::forget(config('constants.TOKEN')) );
    }
    public function handle($request, Closure $next, ...$guards)
    {
        $request->headers->set('accept', 'application/json', true);
        if ($jwt = $request->cookie(config('constants.TOKEN.NAME'))) {
            $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }
        $this->authenticate($request, $guards);

        return $next($request);
    }
}
