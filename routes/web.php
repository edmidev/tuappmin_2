<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\NotificacionUserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Administracion;
use App\Http\Controllers\Api\TwilloController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ZonasComunReservacionController;

use App\Models\TwilloNumber;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('pago/response', function () {
    return view('pages.response');
});

Route::get('twillo/redirect/call/{sid}', [TwilloController::class, 'redirectCall']);

Route::middleware('auth')->group(function () {
    /** Dashboard */
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/count_conjuntos_residenciales', [DashboardController::class, 'count_conjuntos_residenciales']);
    Route::get('/dashboard/count_cantidad_residentes', [DashboardController::class, 'count_residentes']);
    Route::get('/dashboard/get_conjuntos_anuales', [DashboardController::class, 'get_conjuntos_anuales']);
    Route::get('/dashboard/get_residentes_anuales', [DashboardController::class, 'get_residentes_anuales']);
    Route::get('/dashboard/get_ingresos_parkings', [DashboardController::class, 'get_ingresos_parkings']);
    Route::get('/dashboard/get_ingresos_administracion', [DashboardController::class, 'get_ingresos_administracion']);

    /** Notificaciones */
    /** <<<<< Notificaciones >>>>> */
    Route::get('notificaciones/get-take', [NotificacionController::class, 'get_take']);
    Route::post('notificaciones/marcar-vista', [NotificacionController::class, 'marcar_vista']);
    Route::post('notificaciones/marcar-vista-all', [NotificacionController::class, 'marcar_vista_all']);
    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('notificaciones/get-all-paginate', [NotificacionController::class, 'get_all_paginate']);

    /** Usuario */
    Route::resource('usuario', UserController::class)->except(['create', 'edit', 'destroy']);
    Route::get('usuarios/get-all-paginate', [UserController::class, 'get_all_paginate']);
    Route::post('usuarios/change-status/{id}', [UserController::class, 'change_status']);
    Route::get('usuarios/get-all', [UserController::class, 'get_all']);

    /** Residente */
    Route::resource('residente', ResidenteController::class)->except(['create', 'edit']);
    Route::get('residentes/get-all-paginate', [ResidenteController::class, 'get_all_paginate']);
    Route::get('residentes/get-all', [ResidenteController::class, 'get_all']);

    /** Noticia */
    //Route::resource('noticia', NoticiaController::class)->except(['create', 'edit']);
    //Route::get('noticias/get-all-paginate', [NoticiaController::class, 'get_all_paginate']);

    /** Notificacion */
    Route::resource('notificacion_user', NotificacionUserController::class)->except(['create', 'edit']);
    Route::get('notificaciones_users/get-all-paginate', [NotificacionUserController::class, 'get_all_paginate']);

    /** Chat */
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('get-conversaciones', [ChatController::class, 'get_conversaciones']);
    Route::get('messages', [ChatController::class, 'getMessages']);
    Route::post('send-message', [ChatController::class, 'sendMessage']);
    Route::post('nuevo-chat', [ChatController::class, 'nuevoChat']);

    /** Configuracion */
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion/save', [ConfiguracionController::class, 'save'])->name('configuracion.save');

    /** Apartamento */
    Route::resource('apartamento', Administracion\ApartamentoController::class)->except(['create', 'edit']);
    Route::get('apartamentos/get-all-paginate', [Administracion\ApartamentoController::class, 'get_all_paginate']);
    Route::get('apartamentos/get-all', [Administracion\ApartamentoController::class, 'get_all']);
    Route::get('apartamentos/get_all_group_by_bloques', [Administracion\ApartamentoController::class, 'get_all_group_by_bloques']);

    /** Casa */
    Route::resource('casa', Administracion\CasaController::class)->except(['create', 'edit']);
    Route::get('casas/get-all-paginate', [Administracion\CasaController::class, 'get_all_paginate']);
    Route::get('casas/get-all', [Administracion\CasaController::class, 'get_all']);

    /** Zonas comunes */
    Route::resource('zona_comun', Administracion\ZonaComunController::class)->except(['create', 'edit']);
    Route::get('zonas_comunes/get-all-paginate', [Administracion\ZonaComunController::class, 'get_all_paginate']);
    Route::get('zonas_comunes/get-all', [Administracion\ZonaComunController::class, 'get_all']);
    Route::get('zonas_comunes/reservaciones', [Administracion\ZonasComunReservacionController::class, 'index']);
    Route::get('zonas_comunes/reservaciones/get-all-paginate', [Administracion\ZonasComunReservacionController::class, 'get_all_paginate']);
    Route::get('zonas_comunes/horarios_disponibles', [Administracion\ZonaComunController::class, 'get_horarios_disponibles']);

    /** Zona común reservaciones */
    Route::post('zonas_comunes/reservacion', [Administracion\ZonasComunReservacionController::class, 'store']);

    /** Parqueadero */
    Route::resource('parking', Administracion\ParkingController::class)->except(['create', 'edit', 'update']);
    Route::get('parkings/get-all-paginate', [Administracion\ParkingController::class, 'get_all_paginate']);
    Route::get('parkings/get-all', [Administracion\ParkingController::class, 'get_all']);
    Route::post('parkings/export', [Administracion\ParkingController::class, 'export']);

    /** Servicio publicos */
    Route::resource('public_service', Administracion\PublicServiceController::class)->except(['create', 'edit']);
    Route::get('public_services/get-all-paginate', [Administracion\PublicServiceController::class, 'get_all_paginate']);

    /** Correspondencia */
    Route::resource('correspondence', Administracion\CorrespondenceController::class)->except(['create', 'edit']);
    Route::get('correspondences/get-all-paginate', [Administracion\CorrespondenceController::class, 'get_all_paginate']);
    Route::post('correspondences/export', [Administracion\CorrespondenceController::class, 'export']);

    /** Visitante */
    Route::resource('visitante', Administracion\VisitanteController::class)->except(['create', 'edit']);
    Route::get('visitantes/get-all-paginate', [Administracion\VisitanteController::class, 'get_all_paginate']);
    Route::post('visitantes/export', [Administracion\VisitanteController::class, 'export']);

    /** Citofonia */
    Route::resource('citofonia', Administracion\CitofoniaController::class)->except(['create', 'edit']);
    Route::get('citofonias/get-all-paginate', [Administracion\CitofoniaController::class, 'get_all_paginate']);
    Route::post('citofonias/export', [Administracion\CitofoniaController::class, 'export']);

    /** PQRS */
    Route::resource('pqrs', Administracion\PqrsController::class)->except(['create', 'edit']);
    Route::get('pqrss/get-all-paginate', [Administracion\PqrsController::class, 'get_all_paginate']);
    Route::post('pqrss/{id}/chat', [Administracion\PqrsConversacionController::class, 'chat']);
    Route::post('pqrss/send_message', [Administracion\PqrsConversacionController::class, 'send_message']);
    Route::get('pqrss/get-messages', [Administracion\PqrsConversacionController::class, 'get_messages']);
    Route::post('pqrss/finalizar/{id}', [Administracion\PqrsController::class, 'finalizar']);

    /** Comunicados */
    Route::resource('comunicado', Administracion\ComunicadoController::class)->except(['create', 'edit']);
    Route::get('comunicados/get-all-paginate', [Administracion\ComunicadoController::class, 'get_all_paginate']);
    Route::get('comunicados/comentarios-get-all-paginate', [Administracion\ComunicadoController::class, 'comentarios_get_all_paginate']);
    Route::post('comunicados/send_message', [Administracion\ComunicadoController::class, 'send_message']);

    /** Pago */
    Route::resource('pago', Administracion\PagoAdministracionController::class)->except(['create', 'edit']);
    Route::get('pagos/get-all-paginate', [Administracion\PagoAdministracionController::class, 'get_all_paginate']);
    Route::get('pagos/detalles/{pago_month_id}', [Administracion\PagoAdministracionController::class, 'detalles_pago']);
    Route::post('pagos/aprobar', [Administracion\PagoAdministracionController::class, 'aprobar_pago']);
    Route::post('pagos/rechazar', [Administracion\PagoAdministracionController::class, 'rechazar_pago']);

    /** Minuta */
    Route::resource('minuta', Administracion\MinutaController::class)->except(['create', 'edit']);
    Route::get('minutas/get-all-paginate', [Administracion\MinutaController::class, 'get_all_paginate']);
});

Route::get('liberar/numeros', function () {
    $numeros = TwilloNumber::all();
    foreach ($numeros as $numero) {
        $numero->in_use = 0;
        $numero->save();
    }
    return '<h1>Numeros liberados exitosamente</h1>';
});
