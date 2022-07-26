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
        Schema::create('equipment_notes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('application_equipment_id')->nullable();
            $table->foreign('application_equipment_id', 'application_equipment_id_fk_24234111')->references('id')->on('application_equipments');

            $table->double('hours')->unsigned()->default(0);
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_notes');
    }
};
