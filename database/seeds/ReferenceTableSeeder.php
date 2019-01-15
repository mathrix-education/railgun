<?php

use App\Models\Reference;

/**
 * Class ReferenceTableSeeder
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 */
class ReferenceTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromFactory(Reference::class, 500);
    }
}
