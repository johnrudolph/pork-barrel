<?php

namespace App\Events;

use App\Models\Round;
use Thunk\Verbs\Event;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameStarted extends Event
{
    #[StateId(GameState::class)]
    public ?int $game_id = null;

    public function onFire()
    {
        collect(range(1, 8))->each(fn ($n) => 
            Round::create([
                'game_id' => $this->game_id,
                'round_number' => $n,
            ])
        );
    }

    public function apply(GameState $state)
    {
        $state->status = 'in-progress';
    }
}
