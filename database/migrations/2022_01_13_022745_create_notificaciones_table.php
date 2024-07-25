<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_notificacion_id');
            $table->unsignedBigInteger('created_by_id');
            $table->unsignedBigInteger('recurso_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->boolean('visto')->default(0);
            $table->text('message')->nullable();
            $table->string('accion')->nullable();
            $table->timestamps();
            $table->foreign('tipo_notificacion_id')->references('id')->on('tipos_notificaciones')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}
