<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('type');
            $table->text('content')->nullable();
            $table->text('questions')->nullable();
            $table->text('choices')->nullable();
            $table->text('solutions')->nullable();
            $table->text('corrections')->nullable();
            $table->text('options')->nullable();
            $table->boolean('visible');
            $table->boolean('seo_index')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_slug')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('seo_description')->nullable();
            $table->unsignedSmallInteger('order');
            $table->foreignId('activity_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
}
