<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class BaseEvent.
 * Base class for all events.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Events
 */
abstract class BaseEvent
{
    use SerializesModels;
}
