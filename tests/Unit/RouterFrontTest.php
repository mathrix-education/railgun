<?php
namespace Tests\Unit;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Path;
use App\Utils\RouterFront;
use App\Utils\Tree;
use Tests\BaseTestCase;

/**
 * Class RouterFrontTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   Tests\Unit
 */
class RouterFrontTest extends BaseTestCase
{
    /**
     * Test RouterFront::seo_index()
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function testSeoIndex()
    {
        $tree = new Tree();
        $path = Path::retrieve($tree->flatIds("activity"));
        $path->seo_index = true;
        $path->save();

        $path = Path::where("seo_index", "=", true)
            ->firstOrFail();

        $this->assertTrue(RouterFront::seoIndex(
            $path->country,
            $path->class_,
            $path->discipline,
            $path->chapter,
            $path->notion,
            $path->activity
        ));
    }
}
