<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartamentos', function (Blueprint $table) {
            $table->id();
            $table->string('bloque');
            $table->string('apartamento');
            $table->string('nombre_propietario');
            $table->string('telefono')->nullable();
            $table->unsignedBigInteger('conjunto_id');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('apartamentos');
    }
}
