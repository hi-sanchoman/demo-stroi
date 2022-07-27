<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('application_service_id')->nullable();
            $table->foreign('application_service_id', 'application_service_id_fk_24234111')->references('id')->on('application_services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropForeign('application_service_id_fk_24234111');
            $table->dropColumn('application_service_id');
        });
    }
};
