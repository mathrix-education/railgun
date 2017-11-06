<?php
use App\Models\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateReportsTable
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("reports", function (Blueprint $table) {
            $table->increments("id");
            $table->string("content");
            $table->enum("status", Report::STATUSES);
            $table->integer("module_id")->unsigned();
            $table->dateTime("created_at");
            $table->dateTime("updated_at");

            // Foreign keys
            $table->foreign("module_id")->references("id")->on("modules");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->dropForeign("reports_module_id_foreign");
        });

        Schema::dropIfExists("reports");
    }
}
