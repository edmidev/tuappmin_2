<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

/** Trais */
use App\Http\Traits\PagoTrait;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    /** Auth registro, login y verificacion de email */
    Route::post('login', [Api\LoginController::class, 'login']);
    Route::post('logout', [Api\LoginController::class, 'logout']);
});

Route::post('pago/confirmacion', function(Request $request){
    if($request->x_extra1 == 'pago_administracion'){
        return PagoTrait::pagar_administracion($request->all());
    }
    else if($request->x_extra1 == 'pago_zona_comun'){
        return PagoTrait::pagar_zona($request->all());
    }
});

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('auth/verificar-token', [Api\LoginController::class, 'verificar_token']);

    /** Epayco */
    Route::group([
        'middleware' => 'api',
        'prefix' => 'epayco/'
    ], function ($router) {
        Route::get('get_banks', [Api\EpaycoController::class, 'get_banks']);
    });

    /** Perfil */
    Route::group([
        'middleware' => 'api',
        'prefix' => 'perfil/'
    ], function ($router) {
        Route::post('update', [Api\PerfilController::class, 'update']);
        Route::post('update_avatar', [Api\PerfilController::class, 'update_avatar']);
        Route::get('get_perfil', [Api\PerfilController::class, 'get_perfil']);
        Route::get('get_residencias', [Api\PerfilController::class, 'get_residencias']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'apartamento/'
    ], function ($router) {
        Route::get('get_all', [Api\ApartamentoController::class, 'get_all']);
        Route::get('get_all_group_by_bloques', [Api\ApartamentoController::class, 'get_all_group_by_bloques']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'casa/'
    ], function ($router) {
        Route::get('get_all', [Api\CasaController::class, 'get_all']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'public_service/'
    ], function ($router) {
        Route::get('get_all', [Api\PublicServiceController::class, 'get_all']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'config/'
    ], function ($router) {
        Route::get('get_informacion_conjunto', [Api\ConfiguracionController::class, 'get_informacion_conjunto']);
        Route::get('get_residentes_conjuntos', [Api\ConfiguracionController::class, 'get_residentes_conjuntos']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'parking/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\ParkingController::class, 'get_all_paginate']);
        Route::post('save', [Api\ParkingController::class, 'store']);
        Route::get('calcular_total', [Api\ParkingController::class, 'calcular_total']);
        Route::post('reportar_salida', [Api\ParkingController::class, 'reportar_salida']);
        Route::get('count_ocupados', [Api\ParkingController::class, 'count_ocupados']);
        Route::post('send_comprobante', [Api\ParkingController::class, 'send_comprobante']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'correspondence/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\CorrespondenceController::class, 'get_all_paginate']);
        Route::post('save', [Api\CorrespondenceController::class, 'store']);
        Route::post('entregada', [Api\CorrespondenceController::class, 'marcar_entregada']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'visitante/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\VisitanteController::class, 'get_all_paginate']);
        Route::get('count', [Api\VisitanteController::class, 'count']);
        Route::post('save', [Api\VisitanteController::class, 'store']);
        Route::post('reportar_salida/{id}', [Api\VisitanteController::class, 'reportar_salida']);
        Route::post('autorizar_ingreso/{id}', [Api\VisitanteController::class, 'autorizar_ingreso']);
        Route::post('denegar_ingreso/{id}', [Api\VisitanteController::class, 'denegar_ingreso']);
        Route::post('notificar', [Api\VisitanteController::class, 'notificar']);
        Route::get('show_numero_documento', [Api\VisitanteController::class, 'show_numero_documento']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'citofonia/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\CitofoniaController::class, 'get_all_paginate']);
        Route::post('save', [Api\CitofoniaController::class, 'store']);
        Route::post('autorizar_ingreso/{id}', [Api\CitofoniaController::class, 'autorizar_ingreso']);
        Route::post('denegar_ingreso/{id}', [Api\CitofoniaController::class, 'denegar_ingreso']);
        Route::post('notificar', [Api\CitofoniaController::class, 'notificar']);
        Route::post('upload_data', [Api\CitofoniaController::class, 'upload_data']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'pqrs/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\PqrsController::class, 'get_all_paginate']);
        Route::post('save', [Api\PqrsController::class, 'store']);
        Route::get('get-messages', [Api\PqrsConversacionController::class, 'get_messages']);
        Route::post('send_message', [Api\PqrsConversacionController::class, 'send_message']);
        Route::post('delete/{id}', [Api\PqrsController::class, 'destroy']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'comunicado/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\ComunicadoController::class, 'get_all_paginate']);
        Route::get('get-messages', [Api\ComunicadoConversacionController::class, 'get_messages']);
        Route::post('send_message', [Api\ComunicadoConversacionController::class, 'send_message']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'pago_administracion/'
    ], function ($router) {
        Route::get('get_conjunto_informacion', [Api\PagoAdministracionController::class, 'get_conjunto_informacion']);
        Route::get('get_pagos', [Api\PagoAdministracionController::class, 'get_pagos']);
        Route::get('calcular_pago', [Api\PagoAdministracionController::class, 'calcular_pago']);
        Route::post('save_comprobante_transferencia', [Api\PagoAdministracionController::class, 'save_comprobante_transferencia']);
        Route::post('procesar_pago', [Api\PagoAdministracionController::class, 'procesar_pago']);
        Route::post('procesar_pago_pse', [Api\PagoAdministracionController::class, 'procesar_pago_pse']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'zona_comun/'
    ], function ($router) {
        Route::get('get_all', [Api\ZonaComunController::class, 'get_all_zonas_comunes']);
        Route::get('get_horarios_disponibles', [Api\ZonaComunController::class, 'get_horarios_disponibles']);
        Route::post('procesar_pago', [Api\ZonaComunController::class, 'procesar_pago']);
        Route::post('procesar_pago_pse', [Api\ZonaComunController::class, 'procesar_pago_pse']);
        Route::get('reservaciones/get_all_paginate', [Api\ZonaComunReservacionesController::class, 'get_all_paginate']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'minuta/'
    ], function ($router) {
        Route::get('get_all_paginate', [Api\MinutaController::class, 'get_all_paginate']);
        Route::post('save', [Api\MinutaController::class, 'store']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'twillo/'
    ], function ($router) {
        Route::post('mask/number', [Api\TwilloController::class, 'maskNumber']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'notificaciones/'
    ], function ($router) {
        Route::get('get-take', [Api\NotificationController::class, 'get_take']);
        Route::put('save', [Api\NotificationController::class, 'update']);
        Route::get('last/news', [Api\NotificationController::class, 'lastNews']);
    });

    Route::group([
        'middleware' => 'api',
        'prefix' => 'noticias/'
    ], function ($router) {
        Route::get('latest', [Api\NotificationController::class, 'latestNews']);
    });
});
