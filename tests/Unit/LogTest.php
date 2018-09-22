<?php

namespace Tests\Unit;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use Carbon\Carbon;
use Tests\BaseTestCase;

/**
 * Class LogUtils.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.0.3
 */
class LogTest extends BaseTestCase
{
    /**
     * @throws Http400InvalidRequestException
     */
    public function testLogNotExistingEvent()
    {
        $this->expectException(Http400InvalidRequestException::class);

        Log::build("wrong_event");
    }

    /**
     * @throws Http400InvalidRequestException
     */
    public function testLogWithCreatedAt()
    {
        $logDate = new Carbon($this->faker->dateTime()
            ->format("Y-m-d H:i:s"));
        $log = Log::build($this->faker->randomElement(Log::EVENTS), null, [
            "created_at" => $logDate,
        ]);

        $this->assertEquals($logDate, $log->created_at);
    }
}
