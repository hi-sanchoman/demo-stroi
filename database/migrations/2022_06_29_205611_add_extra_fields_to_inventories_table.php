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
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_6663517')->references('id')->on('users');

            $table->dropForeign('application_product_fk_6663516');
            $table->dropColumn('application_product_id');

            $table->dropColumn('quantity');

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
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign('owner_fk_6663517');
            $table->dropColumn('owner_id');
            $table->dropColumn('deleted_at');

            $table->unsignedBigInteger('application_product_id')->nullable();
            $table->foreign('application_product_id', 'application_product_fk_6663516')->references('id')->on('application_products');

            $table->float('quantity', 15, 2);
        });
    }
};
