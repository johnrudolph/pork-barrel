<?php

namespace App\Events;

use App\Models\Game;
use Thunk\Verbs\Event;
use Glhd\Bits\Snowflake;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameCreated extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public $user_id;

    public function apply(GameState $state)
    {
        $state->status = 'awaiting-players';
        $state->players = collect();
        $state->round_ids = collect();
    }

    public function handle()
    {
        Game::create([
            'id' => $this->game_id,
            'code' => rand(10000, 99999),
        ]);

        collect(range(1, 8))->each(fn ($i) => 
            RoundSeeded::fire(
                game_id: $this->game_id,
                round_number: $i,
                round_id: Snowflake::make()->id()
            )
        );
    }
}
