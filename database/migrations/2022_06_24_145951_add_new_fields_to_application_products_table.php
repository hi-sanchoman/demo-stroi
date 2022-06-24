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
        Schema::table('application_products', function (Blueprint $table) {
            $table->bigInteger('price')->unsigned()->default(0);
            $table->text('company')->nullable();
            $table->text('files')->nullable();
            $table->text('accepted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('company');
            $table->dropColumn('nullable');
            $table->dropColumn('accepted_by');
        });
    }
};
