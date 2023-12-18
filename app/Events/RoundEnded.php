<?php

namespace App\Events;

use App\Models\Round;
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

    public function handle()
    {
        $round = Round::find($this->round_id);
        $round->status = 'complete';
        $round->save();

        $state = $this->state(RoundState::class);
        $players = collect($state->game()->players);

        $state->actions_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($a) => $a['hook'] === $state::HOOKS['on_round_ended'])
            ->each(fn ($a) => $a['bureaucrat']::handleInFutureRound(
                PlayerState::load($a['player_id']),
                RoundState::load($this->round_id),
                $a['amount'],
                $a['data'],
            ));

        $state->actions_awarded
            ->reject(fn ($a) => collect($state->blocked_actions)->contains($a))
            ->each(fn ($action) => ActionAppliedAtEndOfRound::fire(
                round_id: $state->id,
                player_id: $action['player_id'],
                amount: $action['amount'],
                bureaucrat: $action['bureaucrat'],
                data: $action['data'],
            ));

        $state->round_modifier::handleOnRoundEnd($state);

        $state->game()->players->each(fn ($p) => PlayerRoundEnded::fire(
            player_id: $p,
            round_id: $this->round_id,
        )
        );
    }
}
