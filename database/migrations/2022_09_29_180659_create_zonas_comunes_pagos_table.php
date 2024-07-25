<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonasComunesPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zonas_comunes_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservacion_id');
            $table->string('x_ref_payco')->nullable();
            $table->string('x_id_invoice')->nullable();
            $table->float('x_amount', 25, 2)->nullable();
            $table->string('x_currency_code', 10)->nullable();
            $table->string('x_cardnumber')->nullable();
            $table->integer('x_quotas')->nullable();
            $table->string('x_respuesta')->nullable();
            $table->string('x_cod_response', 5)->nullable();
            $table->string('x_cod_transaction_state', 5)->nullable();
            $table->string('x_fecha_transaccion')->nullable();
            $table->text('x_signature')->nullable();
            $table->string('x_test_request', 10);
            $table->timestamps();
            $table->foreign('reservacion_id')->references('id')->on('zonas_comunes_reservaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zonas_comunes_pagos');
    }
}
