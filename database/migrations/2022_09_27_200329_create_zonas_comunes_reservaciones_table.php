<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonasComunesReservacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zonas_comunes_reservaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->unsignedBigInteger('horario_id');
            $table->unsignedBigInteger('residente_conjunto_id');
            $table->timestamps();
            $table->foreign('horario_id')->references('id')->on('zonas_comunes_horarios');
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
        Schema::dropIfExists('zonas_comunes_reservaciones');
    }
}
