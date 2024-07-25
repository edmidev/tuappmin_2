<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('residente_conjunto_id')->nullable();
            $table->string('placa');
            $table->string('tipo_vehiculo', 1)->comment('1. Moto. 2. Automóvil');
            $table->string('numero_parking');
            $table->datetime('fecha_ingreso');
            $table->datetime('fecha_salida')->nullable();
            $table->datetime('fecha_estimada_salida')->nullable();
            $table->enum('jornada', [1, 2, 3, 4])->comment('1. Hora. 2. Diurno. 3. Nocturno. 4.Completo');
            $table->double('costo_jornada')->nullable();
            $table->double('total')->nullable();
            $table->unsignedBigInteger('acceso_permitido');
            $table->unsignedBigInteger('acceso_salida')->nullable();
            $table->unsignedBigInteger('conjunto_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('acceso_permitido')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('parkings');
    }
}
