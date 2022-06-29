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
        Schema::create('application_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_product_id')->nullable();
            $table->foreign('application_product_id', 'application_product_fk_6663517')->references('id')->on('application_products');

            $table->string('name')->nullable();
            $table->double('price')->default(0);
            $table->double('quantity')->default(0);
            $table->double('paidTotal')->default(0);
            $table->string('status')->default('draft');
            $table->string('file')->nullable();

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
        Schema::dropIfExists('application_offers');
    }
};
