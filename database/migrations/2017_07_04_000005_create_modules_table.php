<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'modules';

    /**
     * Run the migrations.
     * @table modules
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->enum('type', ['mcq', 'true-false', 'match', 'fill-the-gaps'])->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->text('question')->nullable()->default(null);
            $table->text('answer')->nullable()->default(null);
            $table->text('correction')->nullable()->default(null);
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
