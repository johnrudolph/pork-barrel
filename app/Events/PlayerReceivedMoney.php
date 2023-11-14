<?php

namespace App\Events;

use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerReceivedMoney extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public string $activity_feed_message;

    public int $amount;

    public function handle()
    {
        // activity feed shit? 
    }

    public function apply(PlayerState $state)
    {
        $state->money += $this->amount;
    }
}
