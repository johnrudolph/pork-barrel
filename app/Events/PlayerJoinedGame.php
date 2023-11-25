<?php

namespace App\Events;

use App\Models\Player;
use App\Models\User;
use App\States\GameState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerJoinedGame extends Event
{
    public $user_id;

    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public function handle()
    {
        Player::create([
            'id' => $this->player_id,
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
        ]);

        $user = User::firstWhere('id', $this->user_id);
        $user->current_game_id = $this->game_id;
        $user->save();
    }

    public function applyToGameState(GameState $state)
    {
        $state->players[] = $this->player_id;
    }
}
