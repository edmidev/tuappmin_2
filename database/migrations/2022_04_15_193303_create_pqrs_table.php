<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePqrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pqrs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 1);
            $table->string('image')->nullable();
            $table->text('motivo')->nullable();
            $table->string('estatus', 1)->default('1')->comment('1. Pendiente. 2. Finalizado');
            $table->unsignedBigInteger('residente_conjunto_id');
            $table->timestamps();
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
        Schema::dropIfExists('pqrs');
    }
}
