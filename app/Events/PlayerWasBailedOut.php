<?php

namespace App\Events;

use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerWasBailedOut extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public function apply(PlayerState $state)
    {
        PlayerReceivedMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            amount: 10,
            activity_feed_description: 'You received a bailout. No one needs a stronger safety net than you.',
        );

        $state->has_bailout = false;
    }

    public function handle() 
    {
        dump('player was bailed out');
    }
}
