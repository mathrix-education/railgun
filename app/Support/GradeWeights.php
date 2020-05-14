<?php

declare(strict_types=1);

namespace Railgun\Support;

use Illuminate\Support\Facades\DB;
use stdClass;
use function array_key_exists;
use function collect;
use function is_array;

/**
 * Regarding the grades lineage, generate corresponding "grade weights", based on reference.
 */
class GradeWeights
{
    /** @var int[][] The parent matrix. */
    private array $parents;
    /** @var int[][] The children matrix. */
    private array $children;

    public function __construct()
    {
        [$this->parents, $this->children] = $this->getMatrices();
    }

    /**
     * @return int[][][] The parents and children matrices.
     */
    private function getMatrices(): array
    {
        $parents  = [];
        $children = [];

        DB::table('grades')
            ->select(['id', 'parent_id'])
            ->get()
            ->each(static function (stdClass $line) use (&$parents, &$children): void {
                if ($line->parent_id !== null) {
                    if (
                        array_key_exists($line->parent_id, $parents)
                        && is_array($parents[$line->parent_id])
                    ) {
                        $parents[$line->id][] = $line->parent_id;
                    } else {
                        $parents[$line->id] = [$line->parent_id];
                    }

                    if (
                        array_key_exists($line->parent_id, $children)
                        && is_array($children[$line->parent_id])
                    ) {
                        $children[$line->parent_id] = [$line->id];
                    } else {
                        $children[$line->parent_id][] = $line->id;
                    }
                }
            });

        return [$parents, $children];
    }

    /**
     * Generate the weights based on an entry grade id.
     *
     * @param int   $gradeId The grade id.
     * @param float $weight  The current weight.
     * @param int[] $lines   The already defined lines.
     *
     * @return int[] The completed lines.
     */
    private function buildWeights(int $gradeId, float $weight, array $lines): array
    {
        if (array_key_exists($gradeId, $lines)) {
            // Grade is already in the lines, return immediately to avoid infinite loops.
            return $lines;
        }

        $lines[$gradeId] = $weight;

        if (array_key_exists($gradeId, $this->children)) {
            foreach ($this->children[$gradeId] as $childId) {
                $lines = $this->buildWeights($childId, $weight + 1, $lines);
            }
        }

        if (array_key_exists($gradeId, $this->parents)) {
            $initial = $weight === 0 ? 0.5 : 0;

            foreach ($this->parents[$gradeId] as $parentId) {
                $lines = $this->buildWeights($parentId, $weight + $initial + 1, $lines);
            }
        }

        return $lines;
    }

    /**
     * Generate the weights for a given grade id.
     *
     * @param int $gradeId The grade id.
     *
     * @return int[] The generated weights.
     */
    private function getWeights(int $gradeId): array
    {
        return $this->buildWeights($gradeId, 0, []);
    }

    /**
     * Generate the SQL "case when" statement based on the passed grade id.
     *
     * @param string|int $gradeId The grade id.
     * @param string     $column  The grade_id column.
     *
     * @return string The SQL "case when" statement.
     */
    public function getSql($gradeId, string $column): string
    {
        $weights = $this->getWeights((int)$gradeId);

        $cases = collect($weights)
            ->map(static fn($weight, $id) => "when $id then $weight")
            ->implode("\n");

        return <<<SQL
        case $column
        $cases
        end
        SQL;
    }
}
