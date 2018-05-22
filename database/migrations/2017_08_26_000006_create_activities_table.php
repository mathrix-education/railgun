<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateActivitiesTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("activities", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->string("name_seo")->nullable();
            $table->enum("type", Activity::TYPES);
            $table->boolean("visible")->default(true);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("activities");
    }
}
