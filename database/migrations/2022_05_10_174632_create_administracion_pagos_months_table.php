<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministracionPagosMonthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administracion_pagos_months', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->integer('month');
            $table->unsignedBigInteger('administracion_pago_id');
            $table->timestamps();
            $table->foreign('administracion_pago_id')->references('id')->on('administracion_pagos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('administracion_pagos_months');
    }
}
