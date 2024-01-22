<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use App\States\GameState;
use Glhd\Bits\Snowflake;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

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

        collect(range(1, 8))->each(fn ($i) => RoundSeeded::fire(
            game_id: $this->game_id,
            round_number: $i,
            round_id: Snowflake::make()->id()
        )
        );

        PlayerJoinedGame::fire(
            game_id: $this->game_id,
            player_id: Snowflake::make()->id(),
            user_id: $this->user_id,
            name: User::find($this->user_id)->name,
        );
    }
}
