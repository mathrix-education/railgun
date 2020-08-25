<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserUuidOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('token');
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('users')->update(['uuid' => DB::raw('(SELECT UUID())')]);

        Schema::table('users', static function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->uuid('token')->nullable()->after('scopes');
        });

        DB::table('users')->update(['token' => DB::raw('`uuid`')]);

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
}
