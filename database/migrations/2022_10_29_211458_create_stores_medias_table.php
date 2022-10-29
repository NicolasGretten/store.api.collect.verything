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
        Schema::create('stores_medias', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('storeId');
            $table->string('url');
            $table->string('type');
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('createdAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores_medias');
    }
};