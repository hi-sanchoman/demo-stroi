<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToApplicationStatusesTable extends Migration
{
    public function up()
    {
        Schema::table('application_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id', 'application_fk_6663507')->references('id')->on('applications');
            $table->unsignedBigInteger('application_path_id')->nullable();
            $table->foreign('application_path_id', 'application_path_fk_6663508')->references('id')->on('application_paths');
        });
    }
}
