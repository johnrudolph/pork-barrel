<?php

namespace App\Events;

use App\Models\Game;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameEnded extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function applyToGame(GameState $state)
    {
        $state->status = 'complete';
    }

    public function handle()
    {
        GameUpdated::dispatch($this->game_id);

        Game::find($this->game_id)->players
            ->each(fn ($player) => PlayerGameEnded::fire(
                game_id: $this->game_id,
                player_id: $player->id,
            ));
    }
}
