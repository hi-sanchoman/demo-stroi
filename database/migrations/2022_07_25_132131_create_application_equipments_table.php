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
        Schema::create('application_equipments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->foreign('equipment_id', 'equipment_fk_123123123')->references('id')->on('equipments');

            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id', 'application_fk_123123123')->references('id')->on('applications');

            $table->double('quantity')->unsigned()->default(0);
            $table->double('prepared')->unsigned()->default(0);
            $table->double('delivered')->unsigned()->default(0);

            $table->tinyInteger('is_delivered_by_us')->default(0);
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
        Schema::dropIfExists('application_equipments');
    }
};
