<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Level;

/**
 * Class LevelTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class LevelTableSeeder extends BaseTableSeeder
{
    private const COUNT = 100;
    private const POWER = 1.2;


    /**
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->println("<comment>Seeding:</comment> levels from generation");
        $levels = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $levels[] = [
                "level" => $i,
                "objective" => (int)pow(($i - 1) * 10, self::POWER),
                "youtube_id" => null
            ];
        }

        $this->seedFromArray($levels, Level::class);
        $this->println("<info>Seeded:</info>  levels from generation");
    }
}
