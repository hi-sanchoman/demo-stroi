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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_123123')->references('id')->on('companies');

            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id', 'application_fk_123123')->references('id')->on('applications');

            $table->double('amount')->unsigned()->default(0);
            $table->double('paid')->unsigned()->default(0);
            $table->double('to_be_paid')->unsigned()->default(0);

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
        Schema::dropIfExists('payments');
    }
};
