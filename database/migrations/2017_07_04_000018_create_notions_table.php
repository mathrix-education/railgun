<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotionsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'notions';

    /**
     * Run the migrations.
     * @table notions
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
            $table->integer('chapter_id')->nullable()->default(null);

            $table->index(["chapter_id"], 'fk_notions__chapters_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('chapter_id', 'fk_notions__chapters_idx')
                ->references('disipline_id')->on('chapters')
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
