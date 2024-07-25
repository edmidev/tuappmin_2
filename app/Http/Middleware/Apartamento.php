<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;

class Apartamento
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $residencia = User::find($owner_id);

        if($residencia->tipo == 'Apartamento'){
            return $next($request);
        }

        return abort(404);
    }
}
