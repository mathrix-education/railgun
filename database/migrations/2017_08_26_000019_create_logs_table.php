<?php

use App\Models\Log;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateLogsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("logs", function (Blueprint $table) {
            $table->increments("id");
            $table->enum("event", Log::EVENTS);
            $table->integer("user_id")
                ->unsigned()
                ->nullable();
            $table->string("value")
                ->nullable();
            $table->dateTime("created_at");

            // Polymorphic
            $table->enum("loggable_type", [
                "activity",
                "module",
            ])
                ->nullable();
            $table->integer("loggable_id")
                ->unsigned()
                ->nullable();

            // Foreign keys
            $table->foreign("user_id")
                ->references("id")
                ->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("logs");
    }
}
