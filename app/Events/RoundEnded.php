<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
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

        $state->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => $o->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_round_ended'])
            ->each(fn ($o) => $o->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                $o,
            ));

        $state->offers
            ->filter(fn ($o) => $o->awarded === true
                && ! $o->is_blocked
            )
            ->each(fn ($offer) => ActionAppliedAtEndOfRound::fire(
                round_id: $state->id,
                player_id: $offer->player_id,
                offer: $offer,
            ));

        // @todo is this not its own event? 
        $state->round_modifier::handleOnRoundEnd($state);

        $state->game()->players->each(fn ($p) => PlayerRoundEnded::fire(
            player_id: $p,
            round_id: $this->round_id,
        )
        );

        GameUpdated::dispatch($this->state(RoundState::class)->game_id);
    }
}
