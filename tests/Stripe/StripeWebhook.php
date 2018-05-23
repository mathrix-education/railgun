<?php
namespace Tests\Stripe;

use Carbon\Carbon;
use Tests\BaseTestCase;

abstract class StripeWebhook extends BaseTestCase
{
    protected const EVENT = null;
    protected const STRIPE_API_VERSION = "2016-07-06";

    protected $mockFile;
    /**
     * @var array|null The mock data
     */
    protected $mockData = null;


    public function createApplication()
    {
        return require __DIR__ . "/../../bootstrap/app.php";
    }


    /**
     * @throws \Exception
     */
    public function setUp()
    {
        if (static::EVENT === null) {
            throw new \Exception("EVENT constant cannot be null");
        }

        $this->mockFile = __DIR__ . "/mocks/" . self::STRIPE_API_VERSION . "/" . static::EVENT . ".json";
        $this->mockData = json_decode(file_get_contents($this->mockFile), true);

        parent::setUp();
    }


    /**
     * @param $prefix
     *
     * @return string
     */
    public function generateStripeId($prefix)
    {
        return $prefix . "_" . substr($this->faker->md5, 0, 14);
    }


    /**
     * Make the mock data
     *
     * @return mixed
     */
    public function factory(): array
    {
        $this->mockData["created"] = Carbon::now()->timestamp;
        $this->mockData["id"] = "evt_" . substr($this->faker->md5, 0, 14);
        $this->mockData["api_version"] = self::STRIPE_API_VERSION;

        return $this->mockData;
    }
}
