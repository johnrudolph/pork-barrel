<?php

namespace App\Events;

use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerRoundEnded extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public function apply(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        $state = $this->state(PlayerState::class);

        if ($state->has_bailout && $state->money === 0) {
            PlayerWasBailedOut::fire(
                player_id: $this->player_id,
                round_id: $this->round_id
            );
        }

        PlayerUpdated::dispatch($this->player_id);
    }
}
