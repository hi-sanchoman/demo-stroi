<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('application_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status');
            $table->longText('declined_reason');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
