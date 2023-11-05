<?php

namespace App\Events;

use App\Models\Game;
use App\States\GameState;
use Illuminate\Support\Str;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameCreated extends Event
{
    #[StateId(GameState::class)]
    public ?int $game_id = null;

    public $user_id;

    public function onFire()
    {
        Game::create([
            'id' => $this->game_id,
            'code' => Str::random(4),
        ]);
    }

    public function apply(GameState $state)
    {
        $state->status = 'awaiting-players';
    }
}
