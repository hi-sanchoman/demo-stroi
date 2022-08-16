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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('status')->default('new');
            $table->timestamp('started_at')->nullable();
            $table->tinyInteger('is_hurry')->default(0);

            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_task_fk_243567')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('started_at');
            $table->dropColumn('is_hurry');

            $table->dropForeign('owner_task_fk_243567');
            $table->dropColumn('owner_id');
        });
    }
};
