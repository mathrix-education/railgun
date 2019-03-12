<?php

namespace Tests\Unit;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Path;
use App\Utils\Tree;
use Tests\BaseTestCase;

/**
 * Class PathTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   Tests\Unit
 */
class PathTest extends BaseTestCase
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function testIsIndexed()
    {
        $tree = new Tree();
        $ids = array_merge(
            $tree->flatIds("activity"),
            ["module_id" => null]
        );
        $path = Path::retrieve($ids);
        $path->seo_index = true;
        $path->save();

        $this->assertTrue(Path::isIndexed(
            $path->country->id,
            $path->class_->id,
            $path->discipline->id,
            $path->chapter->id,
            $path->notion->id,
            $path->activity->id
        ));
    }
}
