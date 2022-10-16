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
        Schema::create('contract_comments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id', 'contract_id_comment_fk_243567')->references('id')->on('contracts');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'contract_id_comment_user_fk_243567')->references('id')->on('users');
            
            $table->string('comment')->nullable();

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
        Schema::dropIfExists('contract_comments');
    }
};
