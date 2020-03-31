<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcquisitionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquisitions', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->morphs('product');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'product_type', 'product_id']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisitions');
    }
}
