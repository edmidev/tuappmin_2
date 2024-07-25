<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\User;
use App\Models\Rol;
use App\Models\TokenFirebaseUser;
use App\Models\ResidenteConjunto;

class LoginController extends Controller
{    
    public function login(Request $request)
    {
        $credentials = $request->all();        
        try {
            if($request->acceso != 'appleID'){
                if (! $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password, 'rol_id' => [2, 3, 4, 5], 'status' => 'Activo'])) {
                    return response()->json(['error' => 'Credenciales incorrectas'], 200);
                }
            }
            else{
                if (! $token = JWTAuth::attempt(['user' => $request->email, 'password' => $request->password, 'rol_id' => [2, 3, 4, 5], 'status' => 'Activo'])) {
                    return response()->json(['error' => 'Credenciales incorrectas'], 200);
                }
            }            
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }        
        
        if($request->acceso != 'appleID'){
            $user = User::where('email', $request->email)->first();
        }
        else{
            $user = User::where('user', $request->email)->first();
        }

        /** Obtenemos las residencias que tiene asociada dicho usuario */
        /*$residencias = ResidenteConjunto::join('residentes_informaciones as ri', 'ri.residente_id', '=', 'residentes_conjuntos.id')
        ->join('users as u', 'u.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('residentes_conjuntos.user_id', $user->id)
        ->select([
            'u.name as conjunto', 'u.tipo',
            'ri.*'
        ])
        ->get();*/
        $residencias = [];

        /** Obtenemos el nombre del rol */
        $rol = Rol::find($user->rol_id);
        $user->rol = $rol->name;

        if($user->rol_id != 5){
            /** Obtenemos la casa o el apartamento principal */
            $owner_id = null;
            if($user->rol_id == 2){
                $owner_id = $user->id;
            }
            else{
                $owner_id = $user->owner_id;
            }

            $conjunto = User::find($owner_id);
            $user->tipo = $conjunto->tipo;
            $user->name_conjunto = $conjunto->name;
        }

        /** Validamos que no exista un token de firebase repetido para el mismo usuario */
        $tokenFirebaseUser = TokenFirebaseUser::where('token', $request->token_firebase)
        ->where('user_id', $user->id)->first();
        
        /** En caso de que no exista guardamos la informacion del token fcm */
        if(!$tokenFirebaseUser && $request->token_firebase){
            $tokenFirebaseUser = new TokenFirebaseUser;
            $tokenFirebaseUser->token = $request->token_firebase;
            $tokenFirebaseUser->acceso = 'App';
            $tokenFirebaseUser->user_id = $user->id;
            $tokenFirebaseUser->save();
        }

        return response()->json(compact('token', 'user', 'residencias'), 201);
    }

    public function logout(Request $request)
    {
        $token = $request->token;
        $auth = JWTAuth::parseToken()->authenticate();
        TokenFirebaseUser::where('token', $request->token_firebase)
        ->where('user_id', $auth->id)->delete();

        JWTAuth::manager()->invalidate(new \Tymon\JWTAuth\Token($token), $forceForever = false);

        return response()->json('Desconectado', 201);
    }

    public function verificar_token()
    {
        return response()->json('Ok', 201);
    }
}
