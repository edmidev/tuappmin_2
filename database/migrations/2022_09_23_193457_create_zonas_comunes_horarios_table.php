<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonasComunesHorariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zonas_comunes_horarios', function (Blueprint $table) {
            $table->id();
            $table->time('hora_inicial');
            $table->time('hora_final');
            $table->double('valor');
            $table->enum('status', [1, 2])->default(1)->comment('1. Activo. 2. Inactivo');
            $table->unsignedBigInteger('zona_comun_id');
            $table->timestamps();
            $table->foreign('zona_comun_id')->references('id')->on('zonas_comunes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zonas_comunes_horarios');
    }
}
