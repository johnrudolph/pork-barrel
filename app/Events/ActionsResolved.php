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
        collect($state->actions)
            ->reject(fn ($a) => collect($state->blocked_actions)->contains($a))
            ->each(fn ($action) =>
                $action['class']::resolveFor($action['player_id'], $this->round_id, $action['options'])
            );
    }
}
