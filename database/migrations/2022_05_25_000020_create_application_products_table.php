<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationProductsTable extends Migration
{
    public function up()
    {
        Schema::create('application_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('quantity', 15, 2);
            $table->longText('notes')->nullable();
            $table->boolean('is_delivered_by_us')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
