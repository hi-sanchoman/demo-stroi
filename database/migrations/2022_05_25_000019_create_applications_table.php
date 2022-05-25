<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('issued_at');
            $table->string('kind');
            $table->string('status');
            $table->boolean('is_urgent')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
