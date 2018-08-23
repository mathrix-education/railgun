<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class BaseEvent
 * Base class for all events.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
abstract class BaseEvent
{
    use SerializesModels;
}
