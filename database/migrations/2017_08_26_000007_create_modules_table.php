<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateModulesTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("modules", function (Blueprint $table) {
            $table->increments("id");
            $table->boolean("premium");
            $table->enum("type", Module::TYPES);
            $table->text("content")
                ->nullable();
            $table->text("questions")
                ->nullable();
            $table->text("choices")
                ->nullable();
            $table->text("solutions")
                ->nullable();
            $table->text("corrections")
                ->nullable();
            $table->text("options")
                ->nullable();
            $table->boolean("visible")
                ->default(true);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("modules");
    }
}
