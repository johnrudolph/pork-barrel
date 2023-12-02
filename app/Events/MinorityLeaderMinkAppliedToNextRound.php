<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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
