<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesPExercisesModulesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'activities_p_exercises_modules';

    /**
     * Run the migrations.
     * @table activities_p_exercises_modules
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('activities_p_exercise_id')->nullable()->default(null);
            $table->integer('module_id')->nullable()->default(null);
            $table->integer('order')->nullable()->default(null);

            $table->index(["module_id"], 'fk_activities_p_exercises_modules__modules_idx');

            $table->index(["activities_p_exercise_id"], 'fk_activities_p_exercises_modules__activities_p_exercises_idx');


            $table->foreign('activities_p_exercise_id', 'fk_activities_p_exercises_modules__activities_p_exercises_idx')
                ->references('id')->on('activities_p_exercises')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('module_id', 'fk_activities_p_exercises_modules__modules_idx')
                ->references('id')->on('modules')
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
