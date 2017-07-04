<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostsTagsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'blog_posts_tags';

    /**
     * Run the migrations.
     * @table blog_posts_tags
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('blog_post_id')->nullable()->default(null);
            $table->integer('blog_tag_id')->nullable()->default(null);

            $table->index(["blog_tag_id"], 'fk_blog_posts_tags__tags_idx');

            $table->index(["blog_post_id"], 'fk_blog_posts_tags__posts_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('blog_post_id', 'fk_blog_posts_tags__posts_idx')
                ->references('id')->on('blog_posts')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('blog_tag_id', 'fk_blog_posts_tags__tags_idx')
                ->references('id')->on('blog_tags')
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
