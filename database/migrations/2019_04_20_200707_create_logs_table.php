<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('event');
            $table->text('value')->nullable();
            $table->nullableMorphs('loggable');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
}
