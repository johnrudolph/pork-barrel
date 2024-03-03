<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerAwaitingResults extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public function applyToPlayerState(PlayerState $state)
    {
        $state->status = 'waiting';
    }

    public function fired()
    {
        $this->state(PlayerState::class)->perks
            ->filter(fn ($perk) => $perk::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_awaiting_results'])
            ->each(fn ($perk) => $perk::handlePerkInFutureRound(
                $this->state(PlayerState::class),
                RoundState::load($this->round_id)
            ));
    }

    public function handle()
    {
        PlayerUpdated::dispatch($this->player_id);
    }
}
