<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class DropVotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new CreateVotesTable())->down();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        (new CreateVotesTable())->up();
    }
}
