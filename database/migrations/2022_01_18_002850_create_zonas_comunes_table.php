<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonasComunesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zonas_comunes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('descripcion')->nullable();
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
        Schema::dropIfExists('zonas_comunes');
    }
}
