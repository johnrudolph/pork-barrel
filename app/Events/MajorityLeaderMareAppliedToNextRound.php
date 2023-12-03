<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class MajorityLeaderMareAppliedToNextRound extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public function applyToRoundState(RoundState $state)
    {
        $state->players_with_majority_leader_mare[] = $this->player_id;
    }
}
