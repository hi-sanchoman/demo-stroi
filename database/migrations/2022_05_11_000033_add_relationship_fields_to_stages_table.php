<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToStagesTable extends Migration
{
    public function up()
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->unsignedBigInteger('business_process_id')->nullable();
            $table->foreign('business_process_id', 'business_process_fk_6577857')->references('id')->on('business_processes');
        });
    }
}
