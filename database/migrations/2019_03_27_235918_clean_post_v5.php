<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanPostV5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists("chapter_notion");
        Schema::dropIfExists("activity_notion");
        Schema::dropIfExists("activity_module");
        Schema::dropIfExists("class_discipline");
        Schema::dropIfExists("courses");
        Schema::dropIfExists("quotes");

        // Dropping path_id
        Schema::table("comments", function (Blueprint $table) {
            $table->dropForeign("comments_path_id_foreign");
            $table->dropColumn("path_id");
        });
        Schema::table("marks", function (Blueprint $table) {
            $table->dropForeign("marks_path_id_foreign");
            $table->dropColumn("path_id");
        });
        Schema::dropIfExists("paths");
        Schema::dropIfExists("metas");

        // Dropping class_id
        Schema::table("chapters", function (Blueprint $table) {
            $table->dropForeign("chapters_class_id_foreign");
            $table->dropColumn("class_id");
        });
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign("users_class_id_foreign");
            $table->dropColumn("class_id");
        });
        Schema::table("classes", function (Blueprint $table) {
            $table->dropForeign("classes_parent_id_foreign");
            $table->dropColumn("parent_id");
        });
        Schema::dropIfExists("classes");

        // Dropping family_pack_id
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign("users_family_pack_id_foreign");
            $table->dropColumn("family_pack_id");
        });
        Schema::table("family_packs", function (Blueprint $table) {
            $table->dropForeign("family_packs_parent_id_foreign");
            $table->dropColumn("parent_id");
        });
        Schema::dropIfExists("family_packs");

        // Drop seo fields
        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn(["seo_title", "seo_description", "seo_keywords"]);
        });

        // Drop chapters.activity_count
        Schema::table("chapters", function (Blueprint $table) {
            $table->dropColumn(["activity_count"]);
        });

        Schema::dropIfExists("scores");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We will recreate migrations anyway
    }
}
