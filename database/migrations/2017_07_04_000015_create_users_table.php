<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'users';

    /**
     * Run the migrations.
     * @table users
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('username', 20)->nullable()->default(null);
            $table->string('email', 60)->nullable()->default(null);
            $table->string('firstname', 30)->nullable()->default(null);
            $table->string('lastname', 40)->nullable()->default(null);
            $table->string('password')->nullable()->default(null);
            $table->enum('status', ['registered', 'premium', 'moderator', 'admin'])->nullable()->default(null);
            $table->string('facebook_token')->nullable()->default(null);
            $table->integer('points')->nullable()->default('0');
            $table->integer('daily_goal')->nullable()->default('0');
            $table->text('settings')->nullable()->default(null);
            $table->integer('class_id')->nullable()->default(null);
            $table->integer('rank_id')->nullable()->default(null);

            $table->index(["rank_id"], 'fk_users__ranks_idx');

            $table->index(["class_id"], 'fk_users__classes_idx');

            $table->unique(["id"], 'id_UNIQUE');
            $table->nullableTimestamps();


            $table->foreign('class_id', 'fk_users__classes_idx')
                ->references('id')->on('classes')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('rank_id', 'fk_users__ranks_idx')
                ->references('id')->on('ranks')
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
