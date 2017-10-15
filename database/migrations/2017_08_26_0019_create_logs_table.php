<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('event', [
                'user_registered',
                'user_logged',
                'user_changed_password',
                'user_requested_new_password',
                'user_gathered_points',
                'user_passed_level',
                'user_edited_profile',
                'user_set_daily',
                'user_achieved_daily',
                'user_paid_for_premium',
                'user_added_favorite',
                'user_deleted_favorite',
                'user_marked_activity_as'
            ]);
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('value')->nullable();
            $table->dateTime('created_at');

            // Polymorphic
            $table->enum('loggable_type', [
                'activity',
                'module'
            ])->nullable();
            $table->integer('loggable_id')->unsigned()->nullable();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
