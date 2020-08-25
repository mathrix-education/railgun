<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExerciseProofType extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("alter table exercises modify column `type` enum('mcq', 'proof') not null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('exercises')->update(['type' => 'mcq']);
        DB::statement("alter table exercises modify column `type` enum('mcq') not null");
    }
}
