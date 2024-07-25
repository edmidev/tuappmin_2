<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResidentesConjuntosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('residentes_conjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartamento_id')->nullable();
            $table->unsignedBigInteger('casa_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('conjunto_id');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('residentes_conjuntos');
    }
}
