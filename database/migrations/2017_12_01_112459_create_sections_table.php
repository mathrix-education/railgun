<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateSectionsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->string("slug");
            $table->integer("order");
            $table->integer("training_id")
                ->unsigned();
            $table->timestamps();

            // Foreign keys
            $table->foreign("training_id")
                ->references("id")
                ->on("trainings");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("sections", function (Blueprint $table) {
            $table->dropForeign("sections_training_id_foreign");
        });

        Schema::dropIfExists('sections');
    }
}
