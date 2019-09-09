<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('comments', static function (Blueprint $table) : void {
            $table->bigIncrements('id');
            $table->text('content');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('module_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('module_id')->references('id')->on('modules');
            $table->foreign('parent_id')->references('id')->on('comments');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('comments');
    }
}
