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
        Schema::create('stores', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('accountId');
            $table->string('name');
            $table->string('businessName');
            $table->string('addressId');
            $table->string('phone');
            $table->string('email');
            $table->string('type');
            $table->string('openings');
            $table->string('primaryColor');
            $table->string('SecondaryColor');
            $table->string('logo');
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
        Schema::dropIfExists('stores');
    }
};
