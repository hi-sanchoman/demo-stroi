<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsiblesTable extends Migration
{
    public function up()
    {
        Schema::create('responsibles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order');
            $table->string('status');
            $table->longText('reason')->nullable();
            $table->longText('notes')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
