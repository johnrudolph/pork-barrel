<?php

namespace App\Events;

use App\States\GameState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerReadiedUp extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $game_id;

    public function applyToPlayerState(PlayerState $state)
    {
        $state->status = 'auction';
        $state->current_round_id = $state->game()->round_ids[$state->current_round_number];
        $state->current_round_number += 1;
    }

    public function handle()
    {
        $game = GameState::load($this->game_id);

        if ($game->currentRound()->status === 'complete') {
            $game->nextRound()->start();
        }
    }
}
