<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('labels', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->timestamps();
        });

        Schema::create('labelables', static function (Blueprint $table) {
            $table->unsignedBigInteger('label_id');
            $table->morphs('labelable');
            $table->primary(['label_id', 'labelable_type', 'labelable_id']);
            $table->timestamp('labeled_at');

            $table->foreign('label_id')->references('id')->on('labels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('labelables');
        Schema::dropIfExists('labels');
    }
}
