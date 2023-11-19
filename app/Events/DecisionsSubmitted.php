<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class DecisionsSubmitted extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public array $decisions;

    public function applyToRoundState(RoundState $state)
    {
        collect($this->decisions)
            ->each(function ($decision) use ($state) {
                $action_to_updacollect($state->actions)->firstWhere([
                    'player_id' => $this->player_id,
                    'class' => $decision['class'],
                ]);
            });
    }
}
