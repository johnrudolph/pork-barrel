<?php

namespace App\Events;

use App\States\GameState;
use Glhd\Bits\Snowflake;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;
use Thunk\Verbs\Event;

#[AppliesToState(GameState::class)]
class GameStarted extends Event
{
    public function __construct(
        public int $game_id,
    ) {
        // $this->round_ids ??= Collection::times(8, fn() => Snowflake::make()->id())->values()->all();
    }

    public function applyToGame(GameState $state)
    {
        $state->status = 'in-progress';
    }

    public function fired()
    {
        SeededRounds::fire(
            game_id: $this->game_id,
            round_ids: collect(range(1, 8))->map(fn () => Snowflake::make()->id())->toArray(),
        );
    }
}
