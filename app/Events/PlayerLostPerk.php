<?php

namespace App\Events;

use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerLostPerk extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $perk;

    public function apply(PlayerState $state)
    {
        $state->perks = $state->perks->reject(fn ($p) => $p === $this->perk);
    }
}
