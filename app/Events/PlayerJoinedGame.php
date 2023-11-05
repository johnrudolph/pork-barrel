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
    public ?int $game_id = null;

    #[StateId(PlayerState::class)]
    public ?int $player_id = null;

    public function validate(GameState $state): bool
    {
        if (collect($state->players)->count() > 3) {
            return false;
            // return 'the game is full.';
        }

        if ($state->status !== 'awaiting-players') {
            return false;
            // return 'The game has already started.';
        }

        if (collect($state->players)->contains($this->player_id)) {
            return false;
            // return 'That player is already in this game.';
        }

        return true;
    }

    public function onFire()
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

    public function applyToPlayerState(PlayerState $state)
    {
        $state->name = User::firstWhere('id', $this->user_id)->name;
    }
}
