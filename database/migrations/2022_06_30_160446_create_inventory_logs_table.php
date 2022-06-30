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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->longText('log');
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->foreign('inventory_id', 'inventory_fk_123123')->references('id')->on('inventories');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_123')->references('id')->on('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_logs');
    }
};
