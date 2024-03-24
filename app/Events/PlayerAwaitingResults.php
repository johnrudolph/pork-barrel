<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerAwaitingResults extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToPlayer(PlayerState $state)
    {
        $state->status = 'waiting';
    }

    public function applyToRound(RoundState $state)
    {
        //
    }

    public function fired()
    {
        $this->state(PlayerState::class)->perks
            ->filter(fn ($perk) => $perk::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_awaiting_results'])
            ->each(fn ($perk) => $perk::handlePerkInFutureRound(
                $this->state(PlayerState::class),
                RoundState::load($this->round_id)
            ));

        $this->state(RoundState::class)->offers_from_previous_rounds_that_resolve_this_round
            ->map(fn ($o) => OfferState::load($o))
            ->filter(fn ($o) => $o->player_id === $this->player_id
                && $o->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_awaiting_results']
            )
            ->each(fn ($o) => $o->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                $o
            ));
    }

    public function handle()
    {
        PlayerUpdated::dispatch($this->player_id);
    }
}
