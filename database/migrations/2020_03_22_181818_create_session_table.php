<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session', static function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('discipline_id');
            $table->unsignedBigInteger('chapter_id');
            $table->timestamp('updated_at');

            $table->primary(['user_id', 'discipline_id', 'chapter_id']);

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Foreign keys
            $table->foreign('discipline_id')
                ->references('id')
                ->on('disciplines')
                ->onDelete('cascade');

            // Foreign keys
            $table->foreign('chapter_id')
                ->references('id')
                ->on('chapters')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session');
    }
}
