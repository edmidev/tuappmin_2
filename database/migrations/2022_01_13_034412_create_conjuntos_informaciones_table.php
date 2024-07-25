<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConjuntosInformacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conjuntos_informaciones', function (Blueprint $table) {
            $table->id();
            $table->string('telefono_porteria', 15)->nullable();
            $table->string('direccion')->nullable();
            $table->string('banco')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('tipo_cuenta', 1)->nullable();
            $table->integer('numero_parqueaderos');
            $table->integer('horas_gratis');
            $table->double('valor_hora_adicional_moto');
            $table->double('valor_hora_adicional_carro');
            $table->time('hour_diurno_init')->nullable();
            $table->time('hour_diurno_end')->nullable();
            $table->double('valor_diurno')->nullable();
            $table->time('hour_nocturno_init')->nullable();
            $table->time('hour_nocturno_end')->nullable();
            $table->double('valor_nocturno')->nullable();
            $table->time('hour_completo_init')->nullable();
            $table->time('hour_completo_end')->nullable();
            $table->double('valor_completo')->nullable();
            $table->string('p_cust_id_cliente')->nullable();
            $table->text('p_key')->nullable();
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->unsignedBigInteger('conjunto_id')->unique();
            $table->timestamps();
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
        Schema::dropIfExists('conjuntos_informaciones');
    }
}
