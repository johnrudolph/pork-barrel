<?php

namespace App\Events;

use App\Models\GameTemplates\GameTemplate;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function validateGame(GameState $game)
    {
        $this->assert($game->status !== 'in-progress', 'The game is already active.');
    }

    public function applyToGame(GameState $state)
    {
        $state->status = 'in-progress';
    }

    public function fired()
    {
        if ($this->state(GameState::class)->template === 'tbd') {
            GameTemplateSelected::fire(
                game_id: $this->game_id,
                template: GameTemplate::all()->random(),
            );
        }
    }

    public function handle()
    {
        GameUpdated::dispatch($this->game_id);
    }
}
