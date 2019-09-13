<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Country;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
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
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:post',
        'std:get',
        'std:get:slug',
        'std:patch',
        'rel:get:grades',
    ];

    /**
     * DELETE /countries/{categoryId}
     */
    public function testDelete() : void
    {
        /** @var Country $country */
        $country  = Country::random(); // France
        $fallback = Country::random(['id', '!=', $country->id]);

        $country->grades()->update(['country_id' => $fallback->id]);

        $this->makeRESTJsonRequest('std:delete', null, null, [
            'conditions' => ['id', '=', $country->id],
        ]);
    }
}
