<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteCountriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grades', static function(Blueprint $table): void {
            $table->dropForeign(['country_id']) ;
            $table->dropColumn(['country_id']);
        });

        Schema::dropIfExists('countries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('countries', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->boolean('visible');
            $table->timestamps();
        });

        Schema::table('grades', static function (Blueprint $table): void {
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }
}
