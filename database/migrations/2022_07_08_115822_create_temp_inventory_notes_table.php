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
        Schema::create('temp_inventory_notes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sender_id')->nullable();
            $table->foreign('sender_id', 'sender_inventory_fk_123123')->references('id')->on('inventories');

            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id', 'receiver_inventory_fk_123123')->references('id')->on('inventories');

            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id', 'stock_fk_123123')->references('id')->on('inventory_stocks');

            $table->double('quantity')->default(0);

            $table->string('status')->default('incoming');

            $table->string('notes')->nullable();

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
        Schema::dropIfExists('temp_inventory_notes');
    }
};
