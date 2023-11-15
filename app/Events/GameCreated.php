<?php

namespace App\Events;

use App\Models\Game;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameCreated extends Event
{
    #[StateId(GameState::class)]
    public ?int $game_id = null;

    public $user_id;

    public function handle()
    {
        Game::create([
            'id' => $this->game_id,
            'code' => rand(10000, 99999),
        ]);
    }

    public function apply(GameState $state)
    {
        $state->status = 'awaiting-players';

        $state->players = collect();
    }
}
