<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationLogsTable extends Migration
{
    public function up()
    {
        Schema::create('application_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('log');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
