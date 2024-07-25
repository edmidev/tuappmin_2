<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministracionPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administracion_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad_meses');
            $table->double('total');
            $table->double('valor_administracion');
            $table->double('valor_mora')->nullable();
            $table->float('descuento_pronto_pago')->nullable();
            $table->float('descuento_pago_semestre')->nullable();
            $table->float('descuento_pago_anual')->nullable();
            $table->string('metodo_pago', 1)
            ->comment('1. Transferencia bancaria. 2. Pago con tarjeta. 3. Transaccion PSE');
            $table->string('estatus_pago', 1)
            ->comment('1. En revisión. 2. Rechazado. 3. Aprobado');
            $table->string('comprobante')->nullable();
            $table->unsignedBigInteger('change_estatus_user_id')->nullable();
            $table->unsignedBigInteger('residente_conjunto_id');
            $table->timestamps();
            $table->foreign('residente_conjunto_id')->references('id')->on('residentes_conjuntos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('administracion_pagos');
    }
}
