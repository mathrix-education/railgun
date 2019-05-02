<?php

use Mathrix\Lumen\Bases\BaseTableSeeder;

/**
 * Class LevelTableSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class LevelTableSeeder extends BaseTableSeeder
{
    private const COUNT = 100;
    private const POWER = 1.2;

    public function run()
    {
        $this->output->writeln("<comment>Seeding:</comment> levels from generation");
        $levels = [];

        for ($i = 1; $i <= self::COUNT; ++$i) {
            $levels[] = [
                "level" => $i,
                "objective" => (int)pow(($i - 1) * 10, self::POWER),
                "youtube_id" => null,
            ];
        }

        $this->seedFromArray($levels, "levels");
    }
}
