<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateProductsTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("products", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->string("image")->nullable();
            $table->string("type")->nullable();
            $table->text("description")->nullable();
            $table->float("price")->nullable();
            $table->boolean("visible")->default(true);
            $table->integer("module_id")->unsigned()->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("module_id")->references("id")->on("modules");
        });
        Schema::create("product_product", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("parent_id")->unsigned();
            $table->integer("child_id")->unsigned();
            $table->integer("order");

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("products");
            $table->foreign("child_id")->references("id")->on("products");
        });

        $trainings = DB::table("trainings")->get();
        $sections = DB::table("sections")->get();
        $lessons = DB::table("lessons")->get();

        if ($trainings->isEmpty() || $sections->isEmpty() || $lessons->isEmpty()) {
            return;
        }


        $lastSectionId = $sections->last()->id;
        $sections = $sections->toArray();
        $lastTrainingId = $trainings->last()->id;
        $trainings = $trainings->toArray();
        $lessons = $lessons->toArray();

        $toMigrate = [
            "training" => $trainings,
            "section" => $sections,
            "lesson" => $lessons
        ];
        $products = [];
        $productLinks = [];


        foreach ($toMigrate as $type => $family) {
            foreach ($family as $item) {
                $id = $item->id;
                $price = null;

                switch ($type) {
                    case "training":
                        $price = $item->price / 100;
                        break;
                    case "section":
                        $id += $lastTrainingId;
                        $price = 7;
                        $productLinks[] = [
                            "parent_id" => $item->training_id,
                            "child_id" => $id
                        ];
                        break;
                    case "lesson":
                        $id += $lastTrainingId + $lastSectionId;
                        $productLinks[] = [
                            "parent_id" => $item->section_id + $lastTrainingId,
                            "child_id" => $id
                        ];
                        break;
                }

                $products[] = [
                    "id" => $id,
                    "name" => $item->name ?? $item->title,
                    "slug" => $item->slug,
                    "image" => $item->image ?? null,
                    "type" => $type,
                    "description" => $item->description ?? null,
                    "price" => $price,
                    "visible" => $item->visible,
                    "module_id" => $item->module_id ?? null,
                    "created_at" => $item->created_at ?? Carbon::now(),
                    "updated_at" => $item->updated_at ?? Carbon::now()
                ];
            }
        }

        DB::table("products")->insert($products);
        DB::table("product_product")->insert($productLinks);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("product_product", function (Blueprint $table) {
            $table->dropForeign("product_product_parent_id_foreign");
            $table->dropForeign("product_product_child_id_foreign");
        });
        Schema::dropIfExists("product_product");
        Schema::dropIfExists("products");
    }
}
