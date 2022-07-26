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
        Schema::table('supplies', function (Blueprint $table) {
            $table->unsignedBigInteger('application_equipment_id')->nullable();
            $table->foreign('application_equipment_id', 'application_equipment_id_fk_234234234')->references('id')->on('application_equipments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropForeign('application_equipment_id_fk_234234234');
            $table->dropColumn('application_equipment_id');
        });
    }
};
