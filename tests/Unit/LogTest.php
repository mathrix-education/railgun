<?php
namespace Tests\Unit;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use Carbon\Carbon;
use Tests\BaseTestCase;

/**
 * Class LogTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Unit
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
