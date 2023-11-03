<?php

namespace App\Events;

use App\Models\Player;
use Thunk\Verbs\Event;

class PlayerJoinedGame extends Event
{
    public $game_id;
    public $user_id;

    public function onFire()
    {
        Player::create([
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
        ]);
    }
}