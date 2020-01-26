<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('comments', static function (Blueprint $table) {
            $table->bigInteger('score')->after('content')->default(0);
        });

        $triggers = [
            /** @lang MySQL */<<<MYSQL
            create trigger on_vote_insert_update_comment_score
                after insert on `votes`
                for each row
            begin
                update `comments`
                set `comments`.`score` = (
                    select sum(value) from `votes` where `votes`.`comment_id` = new.`comment_id`
                )
                where `comments`.`id` = new.`comment_id`;
            end;
            MYSQL,
            /** @lang MySQL */<<<MYSQL
            create trigger on_vote_update_update_comment_score
                after update on `votes`
                for each row
            begin
                update `comments`
                set `comments`.`score` = (
                    select sum(value) from `votes` where `votes`.`comment_id` = old.`comment_id`
                )
                where `comments`.`id` = old.`comment_id`;
                
                update `comments`
                set `comments`.`score` = (
                    select sum(value) from `votes` where `votes`.`comment_id` = new.`comment_id`
                )
                where `comments`.`id` = new.`comment_id`;
            end;
            MYSQL,
            /** @lang MySQL */<<<MYSQL
            create trigger on_vote_insert_delete_comment_score
                after delete on `votes`
                for each row
            begin
                update `comments`
                set `comments`.`score` = (
                    select sum(value) from `votes` where `votes`.`comment_id` = old.`comment_id`
                )
                where `comments`.`id` = old.`comment_id`;
            end;
            MYSQL,
        ];

        collect($triggers)->each(fn(string $sql) => DB::unprepared($sql));

        // Fire them once
        DB::unprepared('update votes set comment_id = comment_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (['insert', 'update', 'delete'] as $event) {
            DB::unprepared("drop trigger if exists on_vote_{$event}_update_comment_score");
        }

        Schema::table('comments', static function (Blueprint $table) {
            $table->bigInteger('score')->after('content');
        });
    }
}
