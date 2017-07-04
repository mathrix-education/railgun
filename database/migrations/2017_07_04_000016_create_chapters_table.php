<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChaptersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'chapters';

    /**
     * Run the migrations.
     * @table chapters
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->string('slug')->nullable()->default(null);
            $table->integer('class_id')->nullable()->default(null);
            $table->integer('disipline_id')->nullable()->default(null);

            $table->index(["class_id"], 'fk_chapters__classes_idx');

            $table->index(["disipline_id"], 'fk_chapters__disciplines_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('class_id', 'fk_chapters__classes_idx')
                ->references('id')->on('classes')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('disipline_id', 'fk_chapters__disciplines_idx')
                ->references('id')->on('disciplines')
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
