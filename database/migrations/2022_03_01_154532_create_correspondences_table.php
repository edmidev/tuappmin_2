<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrespondencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('correspondence_type');
            $table->unsignedBigInteger('public_service_id')->nullable();
            $table->unsignedBigInteger('residente_conjunto_id')->nullable();
            $table->string('imagen')->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('acceso_ingreso');
            $table->unsignedBigInteger('acceso_salida')->nullable();
            $table->dateTime('fecha_entregado')->nullable();
            $table->string('estatus', 1)->default(1)->comment('1: En porteria 2: Entregado');
            $table->timestamps();
            $table->foreign('residente_conjunto_id')->references('id')->on('residentes_conjuntos')->onDelete('cascade');
            $table->foreign('acceso_ingreso')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correspondences');
    }
}
