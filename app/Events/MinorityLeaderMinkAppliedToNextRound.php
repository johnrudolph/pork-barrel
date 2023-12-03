<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class MinorityLeaderMinkAppliedToNextRound extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public function applyToRoundState(RoundState $state)
    {
        $state->players_with_minority_leader_mink[] = $this->player_id;
    }
}
