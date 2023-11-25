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
        $players = collect($state->gameState()->players);

        $players->each(fn ($player_id) => $state->actionsWonBy($player_id)
            ->reject(fn ($a) => collect($state->blocked_actions)->contains($a))
            ->each(fn ($action) => ActionAppliedAtEndOfRound::fire(
                round_id: $state->id,
                player_id: $player_id,
                bureaucrat: $action['bureaucrat'],
                data: $action['data'],
            )));

        $players->each(fn ($player_id) => PlayerState::load($player_id)->endRound());

        $state->headline::applyToRoundStateAtEndOfRound($state);
    }
}
