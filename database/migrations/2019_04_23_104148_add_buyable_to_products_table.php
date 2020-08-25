<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBuyableToProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->boolean('buyable')->default(0)->after('visible');
        });

        DB::table('products')->whereNotNull('price')->update(['buyable' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->dropColumn('buyable');
        });
    }
}
