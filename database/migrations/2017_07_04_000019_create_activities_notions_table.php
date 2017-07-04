<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesNotionsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'activities_notions';

    /**
     * Run the migrations.
     * @table activities_notions
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('order')->nullable()->default(null);
            $table->integer('activity_id')->nullable()->default(null);
            $table->integer('notion_id')->nullable()->default(null);

            $table->index(["activity_id"], 'fk_activities_notions__activities_idx');

            $table->index(["notion_id"], 'fk_activities_notions__notions_idx');

            $table->unique(["id"], 'id_UNIQUE');


            $table->foreign('activity_id', 'fk_activities_notions__activities_idx')
                ->references('id')->on('activities')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('notion_id', 'fk_activities_notions__notions_idx')
                ->references('id')->on('notions')
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
