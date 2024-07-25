<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitofoniasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citofonias', function (Blueprint $table) {
            $table->id();
            $table->text('motivo');
            $table->dateTime('fecha_ingreso')->nullable();
            $table->unsignedBigInteger('residente_conjunto_id')->nullable();
            $table->unsignedBigInteger('acceso_ingreso');
            $table->string('estatus_acceso', 1)->comment('1: Esperando autorización. 2: Denegado. 3: Permitido.');
            $table->unsignedBigInteger('change_estatus_user_id')->nullable();
            $table->dateTime('fecha_change_estatus')->nullable();
            $table->unsignedBigInteger('conjunto_id');
            $table->timestamps();
            $table->foreign('acceso_ingreso')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('citofonias');
    }
}
