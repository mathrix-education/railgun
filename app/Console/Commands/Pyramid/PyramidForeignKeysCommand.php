<?php

namespace App\Console\Commands\Pyramid;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidForeignKeys.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PyramidForeignKeysCommand extends BaseCommand
{
    protected $signature = "pyramid:foreign-keys";


    public function handle()
    {
        $this->line("Migrating classes table => grades table");
        $classes = DB::table("classes")->get();
        $now = Carbon::now()->format("Y-m-d H:i:s");
        $classes = $classes->map(function ($class) use ($now) {
            $class->created_at = $now;
            $class->updated_at = $now;

            return (array)$class;
        });
        DB::table("grades")->insert((array)$classes->toArray());
        $this->success("Migrated grades table");


        $this->line("Migrating class_id => grade_id on users table...");
        DB::statement("UPDATE users SET grade_id = class_id;");
        $this->success("Migrated grade_id on users table");


        $this->line("Migrating class_id => grade_id on chapters table...");
        DB::statement("UPDATE chapters SET grade_id = class_id;");
        $this->success("Migrated grade_id on chapters table");


        $this->line("Migrating votable_id => comment_id on votes table...");
        DB::statement("UPDATE votes SET comment_id = votable_id;");
        $this->success("Migrated votable_id on votes table");


        $this->line("Migrating product_product.parent_id => products.parent_id...");
        $links = DB::table("product_product")->get();
        $this->output->progressStart($links->count());
        foreach ($links as $link) {
            DB::table("products")
                ->where("id", "=", $link->child_id)
                ->update(["parent_id" => $link->parent_id]);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->success("Migrated products.parent_id");
    }
}
