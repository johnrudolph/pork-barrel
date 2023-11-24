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
        $state->gameState()->players->each(fn ($player_id) => $state->actionsWonBy($player_id)
            ->reject(fn ($a) => collect($state->blocked_actions)->contains($a))
            ->each(fn ($action) => $action::resolveAtEndOfRoundFor($player_id, $this->round_id)
            )
        );
    }
}
