<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class BaseEvent
 * Base class for all events
 *
 * @author Mathrix Back-End Team
 * @package App\Events
 * @since 1.0.1
 * @copyright Mathrix SA
 */
abstract class BaseEvent
{
    use SerializesModels;
}
