<?php

namespace Tests\API;

use App\Models\Country;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APIStatelessTestCase;

/**
 * Class CountriesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class CountriesTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * DELETE /countries/{categoryId}
     */
    public function testDelete(): void
    {
        /** @var Country $country */
        $country = Country::random(); // France
        $pivot = Country::random(["id", "!=", $country->id]);

        $country->grades()->update(["country_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $country->id]]);
    }


    /**
     * GET /countries/slug/{countrySlug}
     */
    public function testBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }


    /**
     * GET /countries/{countryId}/grades/{page}/{perPage}
     */
    public function testGrades(): void
    {
        $this->assertRestRelationSuccess("grades");
    }
}
