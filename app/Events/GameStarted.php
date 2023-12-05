<?php

namespace App\Events;

use Thunk\Verbs\Event;
use Glhd\Bits\Snowflake;
use App\States\GameState;
use App\Industries\Industry;
use App\Events\PlayerAssignedToIndustry;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;

#[AppliesToState(GameState::class)]
class GameStarted extends Event
{
    public function __construct(
        public int $game_id,
    ) {
    }

    public function applyToGame(GameState $state)
    {
        $state->status = 'in-progress';
    }

    public function fired(GameState $state)
    {
        SeededRounds::fire(
            game_id: $this->game_id,
            round_ids: collect(range(1, 8))->map(fn () => Snowflake::make()->id())->toArray(),
        );

        $industries = Industry::all()->random(collect($state->players)->count());

        collect($state->players)->each(fn ($player_id) => PlayerAssignedToIndustry::fire(
            game_id: $this->game_id,
            player_id: $player_id,
            industry: $industries->pop(),
        ));
    }
}
