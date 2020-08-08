<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupStripeProducts extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->unsignedSmallInteger('price')->after('slug')->nullable();
        });

        foreach (['chapters', 'packs', 'trainings'] as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->string('stripe_price_id')->after('visible')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', static function (Blueprint $table): void {
            $table->dropColumn('stripe_price_id');
        });
        Schema::table('packs', static function (Blueprint $table): void {
            $table->dropColumn('stripe_price_id');
        });
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->dropColumn('stripe_price_id');
            $table->dropColumn('price');
        });
    }
}
