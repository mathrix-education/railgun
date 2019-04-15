<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
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
class Pyramid06ForeignKeysCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:foreign-keys";


    public function handle()
    {
        $this->cell("Migrating classes table => grades table");
        $classes = DB::table("classes")->get();
        $now = Carbon::now()->format("Y-m-d H:i:s");
        $classes = $classes->map(function ($class) use ($now) {
            $class->created_at = $now;
            $class->updated_at = $now;

            return (array)$class;
        });
        DB::table("grades")->insert((array)$classes->toArray());


        $this->cell("Migrating class_id => grade_id on users table");
        DB::statement("UPDATE users SET grade_id = class_id;");


        $this->cell("Migrating class_id => grade_id on chapters table...");
        DB::statement("UPDATE chapters SET grade_id = class_id;");


        $this->cell("Migrating votable_id => comment_id on votes table...");
        DB::statement("UPDATE votes SET comment_id = votable_id;");


        $this->cell("Migrating product_product.parent_id => products.parent_id...");
        $links = DB::table("product_product")->get();
        $this->progressStart($links->count());
        foreach ($links as $link) {
            DB::table("products")
                ->where("id", "=", $link->child_id)
                ->update(["parent_id" => $link->parent_id]);
            $this->progressAdvance();
        }
        $this->progressFinish();
    }
}
