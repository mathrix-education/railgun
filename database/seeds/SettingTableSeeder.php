<?php

use App\Models\Setting;

/**
 * Class SettingTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class SettingTableSeeder extends BaseTableSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->seedFromFactory(Setting::class, random_int(10, 20));
    }
}
