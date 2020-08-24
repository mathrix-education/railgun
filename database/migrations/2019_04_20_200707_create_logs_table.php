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
            $table->timestamp('created_at')->nullable();

            // Foreign keys
            $table->foreignId('user_id')->constrained();
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
