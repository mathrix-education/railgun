<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'blog_posts';

    /**
     * Run the migrations.
     * @table blog_posts
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title')->nullable()->default(null);
            $table->string('slug')->nullable()->default(null);
            $table->text('content')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);

            $table->index(["category_id"], 'fk_blog_posts__blog_categories_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('category_id', 'fk_blog_posts__blog_categories_idx')
                ->references('id')->on('blog_categories')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
