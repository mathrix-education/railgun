<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateUserPurchaseTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.2
 *
 * @copyright Mathrix Education SA
 */
class CreateUserPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::rename("training_user", "purchases");

        Schema::table("purchases", function (Blueprint $table) {
            $table->dropForeign("training_user_training_id_foreign");
            $table->dropIndex("training_user_training_id_foreign");
        });

        Schema::table("purchases", function (Blueprint $table) {
            $table->renameColumn("training_id", "purchasable_id");
            $table->string("purchasable_type")
                ->after("training_id");
            $table->uuid("uuid")
                ->after("id");
            $table->integer("price")
                ->after("id");
            $table->timestamps();
        });

        DB::table("purchases")
            ->update(["purchasable_type" => "training"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table("purchases")
            ->where("purchasable_type", "!=", "training")
            ->delete();

        Schema::table("purchases", function (Blueprint $table) {
            $table->dropColumn("uuid");
            $table->dropColumn("price");
            $table->renameColumn("purchasable_id", "training_id");
            $table->dropColumn("purchasable_type");
            $table->dropColumn("created_at");
            $table->dropColumn("updated_at");
        });

        Schema::rename("purchases", "training_user");

        Schema::table("training_user", function (Blueprint $table) {
            $table->foreign("training_id")
                ->references("id")
                ->on("trainings");
        });
    }
}
