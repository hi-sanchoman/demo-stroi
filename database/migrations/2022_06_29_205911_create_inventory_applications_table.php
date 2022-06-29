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
        Schema::create('inventory_applications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->foreign('inventory_id', 'inventory_fk_6663517')->references('id')->on('inventories');

            $table->unsignedBigInteger('application_product_id')->nullable();
            $table->foreign('application_product_id', 'application_product_fk_66635171')->references('id')->on('application_products');

            $table->double('prepared')->default(0);
            $table->double('accepted')->default(0);
            $table->double('declined')->default(0);

            $table->string('status')->default('waiting');
            $table->string('reason')->nullable();

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
        Schema::dropIfExists('inventory_applications');
    }
};
