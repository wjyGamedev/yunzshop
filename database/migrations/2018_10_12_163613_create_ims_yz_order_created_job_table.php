<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderCreatedJobTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_created_job')) {

            Schema::create('yz_order_created_job', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('state', ['waiting', 'completed', 'canceled']);
                $table->integer('order_id')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_order_created_job')) {

            Schema::drop('yz_order_created_job');
        }
    }

}
