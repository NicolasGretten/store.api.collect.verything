<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('store_id');
            $table->string('day');
            $table->string('from');
            $table->string('to');
            $table->integer('quantity');
            $table->boolean('available')->default('true');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('store_id')->references('id')->on('stores')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores_slots');
    }
};
