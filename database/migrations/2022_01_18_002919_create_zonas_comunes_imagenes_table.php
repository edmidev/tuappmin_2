<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonasComunesImagenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zonas_comunes_imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('imagen');
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
        Schema::dropIfExists('zonas_comunes_imagenes');
    }
}
