<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements("id");
            $table->string("name");
            $table->string("slug");
            $table->string("image")->nullable();
            $table->string("type");
            $table->text("description");
            $table->float("price")->nullable();
            $table->boolean("visible");
            $table->unsignedSmallInteger("order");
            $table->unsignedBigInteger("parent_id");
            $table->unsignedBigInteger("module_id");
            $table->timestamps();

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("products");
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
        Schema::dropIfExists("products");
    }
}
