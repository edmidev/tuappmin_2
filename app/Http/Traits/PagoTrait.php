<?php

namespace App\Http\Traits;

/** Models */

use App\Models\TokenFirebaseUser;
use App\Models\AdministracionPago;
use App\Models\AdministracionPagoMonth;
use App\Models\AdministracionPagoEpayco;
use App\Models\ConjuntoInformacion;
use App\Models\ZonaComunReservacion;
use App\Models\ZonaComunPago;
use App\Models\User;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;

trait PagoTrait
{
    public static function pagar_administracion($request)
    {
        $conjunto_informacion = ConjuntoInformacion::where('conjunto_id', $request['x_extra6'])
        ->select([
            'p_cust_id_cliente', 'p_key'
        ])
        ->first();

        $p_cust_id_cliente = $conjunto_informacion->p_cust_id_cliente;
        $p_key             = $conjunto_informacion->p_key;

        $x_ref_payco      = $request['x_ref_payco'];
        $x_transaction_id = $request['x_transaction_id'];
        $x_amount         = $request['x_amount'];
        $x_currency_code  = $request['x_currency_code'];
        $x_signature      = $request['x_signature'];

        $signature = hash('sha256', $p_cust_id_cliente . '^' . $p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

        $find_pago = AdministracionPagoEpayco::where('x_ref_payco', $x_ref_payco)
        ->where('x_cod_transaction_state', 1)->first();

        if($find_pago){
            header("HTTP/1.1 200 OK");
            return;
        }

        // se valida que el número de orden y el valor coincidan con los valores recibidos
        //Validamos la firma
        if ($x_signature == $signature) {
            if($request['x_cod_response'] == 1){
                $data_registro = json_decode($request['x_extra2']);
                $data['residente_conjunto_id'] = $request['x_extra3'];
                $data['metodo_pago'] = 2;
                $data['estatus_pago'] = 3;
                $data['cantidad_meses'] = $data_registro->a;
                $data['total'] = $request['x_amount'];
                $data['valor_administracion'] = $data_registro->b;
                $data['valor_mora'] = $data_registro->c;
                $data['descuento_pronto_pago'] = $data_registro->d;
                $data['descuento_pago_semestre'] = $data_registro->e;
                $data['descuento_pago_anual'] = $data_registro->f;
                $administracion_pago = AdministracionPago::create($data);

                $month = $data_registro->h;
                $year = $data_registro->g;
                for ($i = 0; $i < $data_registro->a; $i++) {
                    /** Guardamos el registro en la tabla de administracion pagos meses */
                    $pago_month = new AdministracionPagoMonth;
                    $pago_month->year = $year;
                    $pago_month->month = $month;
                    $pago_month->administracion_pago_id = $administracion_pago->id;
                    $pago_month->save();
                    $month++;
                    
                    if($month == 13){
                        $month = 1;
                        $year++;
                    }
                }

                if($data_registro->a == 1){
                    $tipo_notificacion = 9;
                }
                else if($data_registro->a == 6){
                    $tipo_notificacion = 10;
                }
                else if($data_registro->a == 12){
                    $tipo_notificacion = 11;
                }

                $url = str_replace('/', '&', $request['x_extra4']);

                $pago = new AdministracionPagoEpayco;
                $pago->administracion_pago_id = $administracion_pago->id;
                $pago->x_ref_payco = $request['x_ref_payco'];
                $pago->x_id_invoice = $request['x_id_invoice'];
                $pago->x_amount = $request['x_amount'];
                $pago->x_currency_code = $request['x_currency_code'];
                $pago->x_cardnumber = $request['x_cardnumber'];
                $pago->x_quotas = $request['x_quotas'];
                $pago->x_respuesta = $request['x_respuesta'];
                $pago->x_cod_response = $request['x_cod_response'];
                $pago->x_cod_transaction_state = $request['x_cod_transaction_state'];
                $pago->x_fecha_transaccion = $request['x_fecha_transaccion'];
                $pago->x_signature = $request['x_signature'];
                $pago->x_test_request = $request['x_test_request'];
                $pago->save();

                NotificacionTrait::store($tipo_notificacion, $request['x_extra5'], $administracion_pago->id, $url, $request['x_extra6']);

                $owner_id = $request['x_extra6'];

                /** Obtenemos los tokens del conjunto residencial para notificarle */
                $token = TokenFirebaseUser::from('token_firebase_users as tfu')
                ->join('users as u', 'u.id', '=', 'tfu.user_id')
                ->where('tfu.acceso', 'Web')
                ->where(function($query) use($owner_id){
                    return $query->where('u.id', $owner_id)->orWhere('u.owner_id', $owner_id);
                })
                ->pluck('tfu.token')->toArray();

                if(count($token) > 0){
                    /** Enviamos la notificación al usuario */
                    
                    $action = new \stdClass;

                    $send_data = [
                        
                    ];

                    FirebasePushTrait::send('Pago de administración', 'Nuevo pago de administración', $send_data, $token);
                }
            }
            else{
                header("HTTP/1.1 200 OK");
            }
        } 
        else {
            die("Firma no válida");
        }

        header("HTTP/1.1 200 OK");
    }

    public static function pagar_zona($request)
    {
        $conjunto_informacion = ConjuntoInformacion::where('conjunto_id', $request['x_extra6'])
        ->select([
            'p_cust_id_cliente', 'p_key'
        ])
        ->first();

        $p_cust_id_cliente = $conjunto_informacion->p_cust_id_cliente;
        $p_key             = $conjunto_informacion->p_key;

        $x_ref_payco      = $request['x_ref_payco'];
        $x_transaction_id = $request['x_transaction_id'];
        $x_amount         = $request['x_amount'];
        $x_currency_code  = $request['x_currency_code'];
        $x_signature      = $request['x_signature'];

        $signature = hash('sha256', $p_cust_id_cliente . '^' . $p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

        $find_pago = ZonaComunPago::where('x_ref_payco', $x_ref_payco)
        ->where('x_cod_transaction_state', 1)->first();

        if($find_pago){
            header("HTTP/1.1 200 OK");
            return;
        }

        // se valida que el número de orden y el valor coincidan con los valores recibidos
        //Validamos la firma
        if ($x_signature == $signature) {
            if($request['x_cod_response'] == 1){
                $data_registro = json_decode($request['x_extra2']);
                $data['residente_conjunto_id'] = $request['x_extra3'];

                $fecha = $data_registro->d;
                $data['horario_id'] = $data_registro->a;
                $data['fecha_inicio'] = $fecha . ' ' . $data_registro->b;
                $data['fecha_fin'] = $fecha . ' ' . $data_registro->c;

                $reservacion = ZonaComunReservacion::create($data);

                $pago = new ZonaComunPago;
                $pago->reservacion_id = $reservacion->id;
                $pago->x_ref_payco = $request['x_ref_payco'];
                $pago->x_id_invoice = $request['x_id_invoice'];
                $pago->x_amount = $request['x_amount'];
                $pago->x_currency_code = $request['x_currency_code'];
                $pago->x_cardnumber = $request['x_cardnumber'];
                $pago->x_quotas = $request['x_quotas'];
                $pago->x_respuesta = $request['x_respuesta'];
                $pago->x_cod_response = $request['x_cod_response'];
                $pago->x_cod_transaction_state = $request['x_cod_transaction_state'];
                $pago->x_fecha_transaccion = $request['x_fecha_transaccion'];
                $pago->x_signature = $request['x_signature'];
                $pago->x_test_request = $request['x_test_request'];
                $pago->save();

                NotificacionTrait::store(12, $request['x_extra5'], $reservacion->id, 'zonas_comunes/reservaciones', $request['x_extra6']);

                $owner_id = $request['x_extra6'];

                /** Obtenemos los tokens del conjunto residencial para notificarle */
                $token = TokenFirebaseUser::from('token_firebase_users as tfu')
                ->join('users as u', 'u.id', '=', 'tfu.user_id')
                ->where('tfu.acceso', 'Web')
                ->where(function($query) use($owner_id){
                    return $query->where('u.id', $owner_id)->orWhere('u.owner_id', $owner_id);
                })
                ->pluck('tfu.token')->toArray();

                if(count($token) > 0){
                    /** Enviamos la notificación al usuario */
                    
                    $action = new \stdClass;

                    $send_data = [
                        
                    ];

                    FirebasePushTrait::send('Reservación de zona', 'Nueva reservación de zona común', $send_data, $token);
                }
            }
            else{
                header("HTTP/1.1 200 OK");
            }
        } 
        else {
            die("Firma no válida");
        }

        header("HTTP/1.1 200 OK");
    }
}