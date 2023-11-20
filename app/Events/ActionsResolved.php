<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionsResolved extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function apply(RoundState $state)
    {
        $state->gameState()->players->each(fn ($player_id) =>
            $state->actionsAvailableTo($player_id)->each(fn ($action) =>
                $action::resolveFor($player_id, $this->round_id)
            )
        );
    }
}
