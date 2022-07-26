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
        Schema::create('equipment_offers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('application_equipment_id')->nullable();
            $table->foreign('application_equipment_id', 'application_equipment_fk_66635171')->references('id')->on('application_equipments');
            
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->foreign('payment_id', 'payment_fk_12312311')->references('id')->on('payments');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_666351711')->references('id')->on('companies');

            $table->double('price')->default(0);
            $table->double('quantity')->default(0);
            $table->double('paidTotal')->default(0);
            $table->string('status')->default('draft');
            $table->string('file')->nullable();
            $table->double('to_be_paid')->default(0);

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
        Schema::dropIfExists('equipment_offers');
    }
};
