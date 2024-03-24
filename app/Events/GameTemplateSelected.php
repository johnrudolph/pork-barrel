<?php

namespace App\Events;

use App\Models\Game;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameTemplateSelected extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public string $template;

    public function applyToGame(GameState $state)
    {
        $state->template = $this->template;
    }

    public function handle()
    {
        $game = Game::find($this->game_id);
        $game->template = $this->template;
        $game->save();
    }
}
