<?php

declare(strict_types=1);

namespace Tests\Functional\Providers;

use Braintree\Gateway;
use Tests\ApplicationTestCase;
use function config;

/**
 * @coversDefaultClass \Railgun\Providers\BraintreeServiceProvider
 */
class BraintreeServiceProviderTest extends ApplicationTestCase
{
    /**
     * @covers ::register
     */
    public function testRegister(): void
    {
        /** @var Gateway $instance */
        $instance = $this->app->make(Gateway::class);
        $this->assertInstanceOf(Gateway::class, $instance);
        $this->assertEquals(config('services.braintree.environment'), $instance->config->getEnvironment());
        $this->assertEquals(config('services.braintree.merchantId'), $instance->config->getMerchantId());
        $this->assertEquals(config('services.braintree.publicKey'), $instance->config->getPublicKey());
        $this->assertEquals(config('services.braintree.privateKey'), $instance->config->getPrivateKey());
    }
}
