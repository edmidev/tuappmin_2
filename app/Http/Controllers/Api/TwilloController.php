<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;

/** JWT */
use JWTAuth;

/** Models */
use App\Models\TwilloNumber;
use App\Models\TwilloProxy;
use App\Models\User;

class TwilloController extends Controller
{
    public function __construct()
    {
        // $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function maskNumber(Request $request)
    {
        // Validad que se recibe un numero de telefono
        $phoneNumber = $request->phoneNumber;
        if (!$phoneNumber) {
            return response()->json([
                'message'=> 'Debe especificar un numero de telefono valido'
            ], 201);
        }

        // Verificar si ya existe un proxy asociado a este numero y que este activo
        $proxy = TwilloProxy::where('active', 1)
                    ->where('target_number', $phoneNumber)
                    ->with('twilloNumber')
                    ->first();
        // Si existe el proxy, retornar el proxy existente
        if ($proxy) {
            // Actualizar el estatus del numero a EN USO
            $twilloNumber = TwilloNumber::find($proxy->twilloNumber->id);
            $twilloNumber->in_use = 1;
            $twilloNumber->save();
            return $proxy->twilloNumber->phone_number;
        }

        // Obtenemos un numero aleatoriamente de los disponibles
        $twilloNumber = TwilloNumber::where('in_use', 0)->orderByRaw("RAND()")->first();
        // Sino existe un numero disponible, se envia un error
        if (!$twilloNumber) {
            return response()->json([
                'message'=> 'No Existen numeros de telefono disponibles en este momento'
            ], 201);
        }

        // Si hay disponible un numero, marcar en uso para que no se use en otra llamada
        $twilloNumber->in_use = 1;
        $twilloNumber->save();

        // Se crea el proxy
        TwilloProxy::create([
            'twillo_number_id' => $twilloNumber->id,
            'target_number' => $request->phoneNumber,
            'active' => true,
        ]);

        return $twilloNumber->phone_number;
    }

    public function redirectCall(Request $request)
    {
        $twiml = new VoiceResponse();

        $sid = $request->sid;
        $twilloNumber = TwilloNumber::where('sid', $sid)->first();

        $proxy = TwilloProxy::where('twillo_number_id', $twilloNumber->id)
                    ->where('active', 1)
                    ->latest()
                    ->first();

        if(!$twilloNumber || !$proxy) {
            $twiml->say("Su llamada no puede ser conectada, hasta luego.");
            return $twiml;
        }

        // Liberamos el numero para otra llamada
        $twilloNumber->in_use = 0;
        $twilloNumber->save();
        // Deshabilitamos el proxy
        $proxy->active = 0;
        $proxy->save();

        $twiml->say("Redireccionando llamada");
        $twiml->dial('+'.$proxy->target_number);

        return $twiml;
    }
}
