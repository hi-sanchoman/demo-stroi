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
        Schema::create('contract_opened_statuses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id', 'opened_contract_id_fk_123123')->references('id')->on('contracts');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'opened_user_fk_12312312312')->references('id')->on('users');

            $table->string('status')->default('unread');

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
        Schema::dropIfExists('contract_opened_statuses');
    }
};
