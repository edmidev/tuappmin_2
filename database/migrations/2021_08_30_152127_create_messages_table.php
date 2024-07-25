<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('emisor_id');
            $table->unsignedBigInteger('receptor_id');
            $table->string('codigo');
            $table->boolean('visto')->default(0);
            $table->timestamps();
            $table->foreign('emisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receptor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
