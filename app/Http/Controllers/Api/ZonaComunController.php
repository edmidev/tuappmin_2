<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use stdClass;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\ZonaComun;
use App\Models\ZonaComunHorario;
use App\Models\ZonaComunReservacion;
use App\Models\ConjuntoInformacion;

/** Trais */
use App\Http\Traits\Epayco;

class ZonaComunController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');
        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_zonas_comunes(Request $request)
    {
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        /** Obtenemos las zonas comunes */
        $zonas = ZonaComun::where('conjunto_id', $owner_id)
        ->with(['imagenes']);

        /** Filtramos por nombre */
        if($request->name){
            $zonas->where('name', 'like', '%' . $request->name . '%');
        }

        $zonas->orderBy('id', 'ASC');
        $zonas = $zonas->get();

        return response()->json(compact('zonas'), 201);
    }

    public function get_horarios_disponibles(Request $request)
    {
        /** Verificamos los horarios disponibles para el conjunto */
        $horarios = ZonaComunHorario::from('zonas_comunes_horarios as zch')
        ->where('zona_comun_id', $request->zona_comun_id)
        ->where('zch.status', 1)
        ->whereNotExists(function($query) use($request){
            return $query->from('zonas_comunes_reservaciones as zcr')->whereRaw('zcr.horario_id = zch.id')
            ->whereDate('fecha_inicio', $request->fecha);
        })
        ->get();

        return response()->json(compact('horarios'), 201);
    }

    public function procesar_pago(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->select([
            'public_key', 'private_key'
        ])
        ->first();

        if(!$informacion_adicional || !$informacion_adicional->public_key || !$informacion_adicional->private_key){
            return response()->json('No se pudo completar el pago', 200);
        }

        /** Verificamos que el horario de la zona no este reservado */
        $reservacion = ZonaComunReservacion::where('horario_id', $request->horario_id)
        ->whereDate('fecha_inicio', $request->fecha)
        ->first();

        if($reservacion){
            return response()->json('La zona común ya esta reservada para esta fecha.', 200);
        }

        $pago = $request->except([
            'horario_id', 'fecha', 'total',
            'hora_inicial', 'hora_final',
            'year', 'month', 'residencia'
        ]);

        $pago['cardExpYear'] = '20' . $pago['cardExpYear'];
        $pago['description'] = 'Reservación de zona común';
        $pago['dues'] = '1';
        $pago['testMode'] = config('app.debug') == 1 ? true : false;
        $pago['urlConfirmation'] = config('app.url_confirmation');
        $pago['currency'] = 'COP';
        $pago['cardNumber'] = str_replace(' ', '', $pago['cardNumber']);
        $pago['value'] = (string)$request->total;
        $pago['extra1'] = 'pago_zona_comun';

        $object = new stdClass;
        $object->a = (string)$request->horario_id;
        $object->b = (string)$request->hora_inicial;
        $object->c = (string)$request->hora_final;
        $object->d = (string)$request->fecha;
        $pago['extra2'] = json_encode($object);
        $pago['extra3'] = (string)$residencia->residente_conjunto_id;
        $pago['extra4'] = (string)$this->auth->id;
        $pago['extra5'] = (string)$owner_id;

        $data = Epayco::get_token($informacion_adicional->public_key, $informacion_adicional->private_key);

        if(!$data || isset($data->error)){
            return response()->json(!$data->error ? 'Ocurrio un error. Intentalo mas tarde.' : $data->error, 200);
        }

        /** Guardamos el token */
        $token = $data->token;
        
        /** Procesamos el pago */
        $pago = Epayco::payment_card($token, $pago);

        $pago = json_encode($pago);

        return response()->json(compact('pago'), 201);
    }

    public function procesar_pago_pse(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->select([
            'public_key', 'private_key'
        ])
        ->first();

        if(!$informacion_adicional || !$informacion_adicional->public_key || !$informacion_adicional->private_key){
            return response()->json('No se pudo completar el pago', 200);
        }

        /** Verificamos que el horario de la zona no este reservado */
        $reservacion = ZonaComunReservacion::where('horario_id', $request->horario_id)
        ->whereDate('fecha_inicio', $request->fecha)
        ->first();

        if($reservacion){
            return response()->json('La zona común ya esta reservada para esta fecha.', 200);
        }

        $pago = $request->except([
            'horario_id', 'fecha', 'total',
            'hora_inicial', 'hora_final',
            'year', 'month', 'residencia'
        ]);

        $pago['description'] = 'Reservación de zona común';
        $pago['dues'] = '1';
        $pago['testMode'] = config('app.debug') == 1 ? true : false;
        $pago['urlConfirmation'] = config('app.url_confirmation');
        $pago['methodConfimation'] = 'POST';
        $pago['urlResponse'] = url('/') . '/pago/response';
        $pago['currency'] = 'COP';
        $pago['value'] = (string)$request->total;
        $pago['extra1'] = 'pago_zona_comun';

        $object = new stdClass;
        $object->a = (string)$request->horario_id;
        $object->b = (string)$request->hora_inicial;
        $object->c = (string)$request->hora_final;
        $object->d = (string)$request->fecha;
        $pago['extra2'] = json_encode($object);
        $pago['extra3'] = (string)$residencia->residente_conjunto_id;
        $pago['extra4'] = (string)$this->auth->id;
        $pago['extra5'] = (string)$owner_id;

        $data = Epayco::get_token($informacion_adicional->public_key, $informacion_adicional->private_key);

        if(!$data || isset($data->error)){
            return response()->json(!$data->error ? 'Ocurrio un error. Intentalo mas tarde.' : $data->error, 200);
        }

        /** Guardamos el token */
        $token = $data->token;
        
        /** Procesamos el pago */
        $pago = Epayco::payment_pse($token, $pago);

        $pago = json_encode($pago);

        return response()->json(compact('pago'), 201);
    }
}
