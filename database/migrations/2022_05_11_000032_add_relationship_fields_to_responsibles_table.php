<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToResponsiblesTable extends Migration
{
    public function up()
    {
        Schema::table('responsibles', function (Blueprint $table) {
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->foreign('stage_id', 'stage_fk_6577912')->references('id')->on('stages');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id', 'role_fk_6577795')->references('id')->on('roles');
            $table->unsignedBigInteger('specific_user_id')->nullable();
            $table->foreign('specific_user_id', 'specific_user_fk_6577796')->references('id')->on('users');
        });
    }
}
