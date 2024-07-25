<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class PermisosRolesApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $auth = JWTAuth::parseToken()->authenticate();

        foreach ($roles as $rol) {
            if ($auth->rol_id == $rol) {
                return $next($request);
            }
        }

        abort(403);
    }
}
