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
        Schema::create('contract_paths', function (Blueprint $table) {
            $table->id();

            $table->string('position');
            $table->string('type')->default('default');
            $table->integer('order')->unsigned()->default(0);

            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->foreign('responsible_id', 'responsible_fk_61225')->references('id')->on('users');

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
        Schema::dropIfExists('contract_paths');
    }
};
