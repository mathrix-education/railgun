<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeExercises extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws JsonException
     */
    public function up(): void
    {
        Schema::table('exercises', static function (Blueprint $table): void {
            $table->json('content')->after('explanations');
        });

        DB::table('exercises')->get()->each(static function (stdClass $exercise): void {
            $content = [];

            $exercise->questions    = json_decode($exercise->questions, false, 512, JSON_THROW_ON_ERROR);
            $exercise->choices      = json_decode($exercise->choices, false, 512, JSON_THROW_ON_ERROR);
            $exercise->solutions    = json_decode($exercise->solutions, false, 512, JSON_THROW_ON_ERROR);
            $exercise->explanations = json_decode($exercise->explanations, false, 512, JSON_THROW_ON_ERROR);

            foreach ($exercise->questions as $i => $q) {
                if ($exercise->type !== 'mcq') {
                    break;
                }

                $content[] = [
                    'kind'         => 'mcq',
                    'statement'    => $exercise->questions[$i],
                    'choices'      => collect($exercise->choices[$i])
                        ->map(static function (array $label, int $j) use ($exercise, $i) {
                            return [
                                'label'   => $label,
                                'correct' => in_array($j, $exercise->solutions[$i], true),
                            ];
                        })
                        ->toArray(),
                    'explanations' => $exercise->explanations[$i],
                ];
            }

            DB::table('exercises')
                ->where('id', '=', $exercise->id)
                ->update(['content' => $content]);
        });

        Schema::table('exercises', static function (Blueprint $table): void {
            $table->dropColumn(['type', 'questions', 'choices', 'solutions', 'explanations']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', static function (Blueprint $table): void {
            $table->json('explanations')->after('id');
            $table->json('solutions')->after('id');
            $table->json('choices')->after('id');
            $table->json('questions')->after('id');
            $table->enum('type', ['mcq', 'proof'])->after('id')->default('mcq');
        });

        DB::statement('alter table `exercises` alter column `type` drop default');
    }
}
