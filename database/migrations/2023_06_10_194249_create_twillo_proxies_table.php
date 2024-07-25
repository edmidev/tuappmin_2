<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilloProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twillo_proxies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('twillo_number_id');
            $table->string('target_number');
            $table->boolean('active')->default(false);

            $table->foreign('twillo_number_id')->references('id')->on('twillo_numbers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twillo_proxies');
    }
}
