<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConjuntosInformacionPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conjuntos_informacion_pagos', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->double('valor_administracion');
            $table->string('limite_pago')->comment('1: Primer dia del mes. 2: Mediados del mes. 3: Ultimo dia del mes.');
            $table->float('interes_mora')->nullable();
            $table->float('descuento_pronto_pago')->nullable();
            $table->integer('limite_pronto_pago')->nullable();
            $table->float('descuento_pago_semestre')->nullable();
            $table->float('descuento_pago_anual')->nullable();
            $table->unsignedBigInteger('conjunto_id');
            $table->timestamps();
            $table->foreign('conjunto_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conjuntos_informacion_pagos');
    }
}
