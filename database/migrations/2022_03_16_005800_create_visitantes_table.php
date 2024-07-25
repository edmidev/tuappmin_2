<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('numero_documento');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('rh', 10)->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('temperatura');
            $table->string('imagen', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_ingreso')->nullable();
            $table->dateTime('fecha_salida')->nullable();
            $table->string('tipo', 1)->comment('1: Visitante. 2: Domiciliario. 3: Técnico.');
            $table->unsignedBigInteger('residente_conjunto_id')->nullable();
            $table->unsignedBigInteger('acceso_ingreso');
            $table->unsignedBigInteger('acceso_salida')->nullable();
            $table->string('estatus_acceso', 1)->comment('1: Esperando autorización. 2: Denegado. 3: Permitido.');
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
        Schema::dropIfExists('visitantes');
    }
}
