<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Zero\Testing\Traits\DatabaseTrait;
use Tests\BaseTestCase;
use function time;

/**
 * @coversDefaultClass \App\Console\Commands\CacheCleanCommand
 */
class CleanCacheCommandTest extends BaseTestCase
{
    use DatabaseTransactions;
    use DatabaseTrait;

    /**
     * @covers ::handle
     */
    public function testHandle()
    {
        $dataset = [
            [
                'key'        => 'test-expired',
                'value'      => 'val',
                'expiration' => time() - 10,
            ],
            [
                'key'        => 'test-not-expired',
                'value'      => 'val',
                'expiration' => time() + 3600,
            ],
        ];

        $table = DB::table('cache');
        $table->insert($dataset);

        $this->assertEquals(0, $this->artisan('cache:clean'));
        $this->assertNotInDatabase('cache', $dataset[0]);
        $this->assertInDatabase('cache', $dataset[1]);
    }
}
