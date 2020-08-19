<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserIdColumnOnSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->after('current_period_end');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });

        collect()
            ->merge(
                DB::table('subscriptions')
                    ->join('users', 'customer_id', '=', 'stripe_id')
                    ->selectRaw('subscriptions.id as id, users.id as user_id')
                    ->get(),
            )
            ->merge(
                DB::table('subscriptions')
                    ->join('users', 'customer_id', '=', 'braintree_id')
                    ->selectRaw('subscriptions.id as id, users.id as user_id')
                    ->get(),
            )
            ->each(static function (stdClass $subscription): void {
                DB::table('subscriptions')->where('id', '=', $subscription->id)
                    ->update(['user_id' => $subscription->user_id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', static function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
