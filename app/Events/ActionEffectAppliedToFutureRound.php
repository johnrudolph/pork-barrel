<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionEffectAppliedToFutureRound extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public string $bureaucrat;

    public int $amount;

    public ?array $data = null;

    public string $hook;

    public function applyToRound(RoundState $state)
    {
        $state->actions_from_previous_rounds_that_resolve_this_round
            ->push([
                'player_id' => $this->player_id,
                'bureaucrat' => $this->bureaucrat,
                'amount' => $this->amount,
                'data' => $this->data,
                'hook' => $this->hook,
            ]);
    }
}
