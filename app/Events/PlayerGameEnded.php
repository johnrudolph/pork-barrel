<?php

namespace App\Events;

use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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

        Player::find($this->player_id)->update([
            'current_game_id' => null,
        ]);
    }
}
