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
            $table->string('account_id');
            $table->string('name');
            $table->string('business_name');
            $table->string('address_id');
            $table->string('phone');
            $table->string('email');
            $table->string('type');
            $table->string('openings');
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->string('logo');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
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
