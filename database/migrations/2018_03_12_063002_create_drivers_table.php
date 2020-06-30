<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->integer('rang_id')->nullable();

            $table->string('bonus')->nullable();
            $table->string('description')->nullable();
            $table->integer('transport_id')->nullable();
            $table->string('transport_name')->nullable();
            $table->string('transport_info')->nullable();
            $table->string('transport_number')->nullable();

            $table->string('token')->unique();
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
        Schema::dropIfExists('drivers');
    }
}
