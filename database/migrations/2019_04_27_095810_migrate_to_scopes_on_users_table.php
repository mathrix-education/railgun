<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToScopesOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->json('scopes')->nullable()->after('type');
        });

        $users = DB::table('users');
        $users->where('type', '=', 'admin')
            ->update(['scopes' => json_encode(['*'])]);
        $users->where('type', '!=', 'admin')
            ->update(['scopes' => json_encode(['logged'])]);

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('type')->after('password');
            $table->dropColumn('scopes');
        });
    }
}
