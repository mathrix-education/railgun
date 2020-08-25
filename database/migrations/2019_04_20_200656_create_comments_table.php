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
    public function up(): void
    {
        Schema::create('comments', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->text('content');
            $table->foreignId('module_id')->constrained();
            $table->foreignId('parent_id')->nullable()->constrained('comments');
            $table->foreignId('user_id')->constrained();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
}
