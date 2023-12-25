<?php

namespace App\Events;

use App\Models\User;
use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\Events\GameUpdated;
use App\States\PlayerState;
use App\Industries\Industry;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerJoinedGame extends Event
{
    public $user_id;

    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public function applyToGameState(GameState $state)
    {
        $state->players->push($this->player_id);
    }

    public function applyToPlayerState(PlayerState $state)
    {
        $state->game_id = $this->game_id;
        $state->current_round_id = $this->state(GameState::class)->round_ids->first();
        $state->current_round_number = 1;
        $state->industry = Industry::unusedRandomIndustry($this->state(GameState::class));
    }

    public function handle()
    {
        Player::create([
            'id' => $this->player_id,
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
        ]);

        $user = User::find($this->user_id);
        $user->current_game_id = $this->game_id;
        $user->save();

        // GameUpdated::dispatch($this->game_id);
    }
}
