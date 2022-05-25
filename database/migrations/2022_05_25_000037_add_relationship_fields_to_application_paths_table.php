<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToApplicationPathsTable extends Migration
{
    public function up()
    {
        Schema::table('application_paths', function (Blueprint $table) {
            $table->unsignedBigInteger('construction_id')->nullable();
            $table->foreign('construction_id', 'construction_fk_6663514')->references('id')->on('constructions');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->foreign('responsible_id', 'responsible_fk_6663515')->references('id')->on('users');
        });
    }
}
