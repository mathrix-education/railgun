<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Setting;


/**
 * Class SettingTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class SettingTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Setting::class,
            "count" => random_int(10, 20)
        ]);
    }
}
