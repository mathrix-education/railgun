<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClonesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clones', static function (Blueprint $table): void {
            $table->string('type');
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('clone_id');
            $table->primary(['type', 'source_id', 'clone_id']);
            $table->index(['type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clones');
    }
}
