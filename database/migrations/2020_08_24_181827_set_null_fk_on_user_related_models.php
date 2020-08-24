<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetNullFkOnUserRelatedModels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('acquisitions',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table acquisitions modify user_id bigint unsigned null;');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('comments',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table comments modify user_id bigint unsigned null;');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('notes',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table notes modify user_id bigint unsigned null;');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('subscriptions',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table subscriptions modify user_id bigint unsigned null;');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquisitions',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table acquisitions modify user_id bigint unsigned not null;');
            $table->foreignId('user_id')->constrained();
        });

        Schema::table('comments',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table comments modify user_id bigint unsigned not null;');
            $table->foreignId('user_id')->constrained();
        });

        Schema::table('notes',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table notes modify user_id bigint unsigned not null;');
            $table->foreignId('user_id')->constrained();
        });

        Schema::table('subscriptions',static function(Blueprint $table): void {
            $table->dropForeign(['user_id']);
            DB::statement('alter table subscriptions modify user_id bigint unsigned not null;');
            $table->foreignId('user_id')->constrained();
        });
    }
}
