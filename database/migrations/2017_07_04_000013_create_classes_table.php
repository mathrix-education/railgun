<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'classes';

    /**
     * Run the migrations.
     * @table classes
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
            $table->integer('order')->nullable()->default(null);
            $table->integer('parent_id')->nullable()->default(null);
            $table->integer('country_id')->nullable()->default(null);

            $table->index(["country_id"], 'fk_classes__countries_idx');

            $table->index(["parent_id"], 'fk_classes__classes_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('parent_id', 'fk_classes__classes_idx')
                ->references('id')->on('classes')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('country_id', 'fk_classes__countries_idx')
                ->references('id')->on('countries')
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
