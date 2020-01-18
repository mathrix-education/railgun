<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('interactions', static function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->morphs('interactable');
            $table->enum('comprehension', ['blur', 'clear'])->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamps();

            $table->primary(['user_id', 'interactable_type', 'interactable_id']);

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
}
