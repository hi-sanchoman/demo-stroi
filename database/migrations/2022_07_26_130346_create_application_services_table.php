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
        Schema::create('application_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id', 'application_fk_555')->references('id')->on('applications');

            $table->string('service')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
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
        Schema::dropIfExists('application_services');
    }
};
