<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSectionsTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->string("slug");
            $table->integer("order");
            $table->integer("training_id")->unsigned();
            $table->timestamps();

            // Foreign keys
            $table->foreign("training_id")->references("id")->on("trainings");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("sections", function (Blueprint $table) {
            $table->dropForeign("sections_training_id_foreign");
        });

        Schema::dropIfExists('sections');
    }
}
