<?php

use App\Models\Level;

class LevelTableSeeder extends BaseTableSeeder
{
    private const COUNT = 100;
    private const POWER = 1.2;


    public function run()
    {
        $levels = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $levels[] = [
                "level" => $i,
                "objective" => (int)pow($i, self::POWER) * 10,
                "youtube_id" => null
            ];
        }

        $this->seedFromArray($levels, Level::class);
    }
}