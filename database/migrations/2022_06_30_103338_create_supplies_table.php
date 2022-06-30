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
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('construction_id')->nullable();
            $table->foreign('construction_id', 'construction_fk_123')->references('id')->on('constructions');

            $table->unsignedBigInteger('application_product_id')->nullable();
            $table->foreign('application_product_id', 'application_product_fk_123')->references('id')->on('application_products');

            $table->float('quantity', 15, 2);

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
        Schema::dropIfExists('supplies');
    }
};
