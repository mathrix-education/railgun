<?php
namespace Database\seeds;

/**
 * Class LevelTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class LevelTableSeeder extends BaseTableSeeder
{
    private const COUNT = 100;
    private const POWER = 1.2;

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->output->writeln("<comment>Seeding:</comment> levels from generation");
        $levels = [];

        for ($i = 1; $i <= self::COUNT; ++$i) {
            $levels[] = [
                "level" => $i,
                "objective" => (int) pow(($i - 1) * 10, self::POWER),
                "youtube_id" => null,
            ];
        }

        $this->seedFromArray($levels, "levels");
        $this->output->writeln("<info>Seeded:</info>  levels from generation");
    }
}
