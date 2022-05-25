<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationPathsTable extends Migration
{
    public function up()
    {
        Schema::create('application_paths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('position');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
