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
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->double('price')->nullable();
            $table->string('file_price')->nullable();
            $table->string('address')->nullable();
            $table->string('company_bin')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_iik')->nullable();
            $table->string('company_bank')->nullable();
            $table->string('company_ceo')->nullable();
            $table->string('file_smeta')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('nds')->nullable();
            $table->double('warranty_amount')->nullable();
            $table->string('warranty_job_period')->nullable();
            $table->string('warranty_materials_period')->nullable();

            $table->string('certificate')->nullable();

            $table->double('deposit')->nullable();
            $table->string('rent_reason')->nullable();
            $table->text('rent_addons')->nullable();

            $table->string('file_passport')->nullable();
            $table->string('equipment_crew')->nullable();
            $table->text('equipment_price_addons')->nullable();
            $table->string('equipment_working_hours')->nullable();
            $table->string('overrate')->nullable();
            $table->text('equipment_responsible')->nullable();
            $table->text('requisites')->nullable();
            
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('date_start');
            $table->dropColumn('date_end');
            $table->dropColumn('price');
            $table->dropColumn('file_price');
            $table->dropColumn('address');
            $table->dropColumn('company_bin');
            $table->dropColumn('company_address');
            $table->dropColumn('company_iik');
            $table->dropColumn('company_bank');
            $table->dropColumn('company_ceo');
            $table->dropColumn('file_smeta');
            $table->dropColumn('payment_method');
            $table->dropColumn('nds');
            $table->dropColumn('warranty_amount');
            $table->dropColumn('warranty_job_period');
            $table->dropColumn('warranty_materials_period');

            $table->dropColumn('certificate');

            $table->dropColumn('deposit');
            $table->dropColumn('rent_reason');
            $table->dropColumn('rent_addons');

            $table->dropColumn('file_passport');
            $table->dropColumn('equipment_crew');
            $table->dropColumn('equipment_price_addons');
            $table->dropColumn('equipment_working_hours');
            $table->dropColumn('overrate');
            $table->dropColumn('equipment_responsible');
            $table->dropColumn('requisites');

            $table->dropColumn('notes');
        });
    }
};
