<?php

namespace App\Events;

use App\Models\Player;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerGameEnded extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public function apply(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        PlayerUpdated::dispatch($this->player_id);

        Player::find($this->player_id)->user->update([
            'current_game_id' => null,
        ]);
    }
}
