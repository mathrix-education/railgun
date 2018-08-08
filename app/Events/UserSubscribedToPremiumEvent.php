<?php
namespace App\Events;

use App\Models\User;

/**
 * Class UserSubscribedToPremiumEvent
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Events
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class UserSubscribedToPremiumEvent extends BaseEvent
{
    /** @var User $user */
    private $user;
    /** @var string $plan */
    private $plan;


    public function __construct(User $user, string $plan)
    {
        $this->user = $user;
        $this->plan = $plan;
    }

    public function handle(UserSubscribedToPremiumEvent $event)
    {
    }
}
