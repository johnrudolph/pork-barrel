<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundEnded extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {
        $state->status = 'complete';
    }

    public function fired(RoundState $state)
    {
        $state->headline::applyToRoundStateAtEndOfRound($state);

        collect($state->gameState()->players)
            ->each(fn ($player_id) => PlayerState::load($player_id)->endRound());
    }
}
