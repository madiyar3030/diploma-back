<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_address_id');
            $table->integer('to_address_id');
            $table->timestamp('date');
            $table->string('count');
            $table->integer('count_type_id');
            $table->string('weight');
            $table->integer('weight_type_id');
            $table->string('price');
            $table->text('description');
            $table->integer('client_id');
            $table->integer('driver_id');
            $table->string('type');
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
        Schema::dropIfExists('shipping_orders');
    }
}
