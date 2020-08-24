<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('votes', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->tinyInteger('value');
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);

            // Foreign keys
            $table->foreignId('comment_id')->constrained();
            $table->foreignId('user_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
}
