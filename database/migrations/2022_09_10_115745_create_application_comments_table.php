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
        Schema::create('application_comments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('application_id')->nullable();
            $table->foreign('application_id', 'application_comment_fk_243567')->references('id')->on('applications');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'application_comment_user_fk_243567')->references('id')->on('users');
            
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
        Schema::dropIfExists('application_comments');
    }
};
