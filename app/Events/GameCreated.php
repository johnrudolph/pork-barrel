<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Player;
use Thunk\Verbs\Event;
use Illuminate\Support\Str;

class GameCreated extends Event
{
    public $user_id;

    public function onFire()
    {
        $game = Game::create([
            'code' => Str::random(4),
        ]);

        PlayerJoinedGame::fire(
            game_id: $game->id,
            user_id: $this->user_id,
        );
    }
}