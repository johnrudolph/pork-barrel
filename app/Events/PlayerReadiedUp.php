<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\RoundState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerReadiedUp extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(GameState::class)]
    public int $game_id;

    public function applyToPlayer(PlayerState $state)
    {
        $state->status = 'auction';
        $state->current_round_id = $this->state(RoundState::class)->next()->id;
    }

    public function applyToRound(RoundState $state)
    {
        //
    }

    public function applyToGame(GameState $state)
    {
        //
    }

    public function handle()
    {
        $game = $this->state(GameState::class);
        $next_round = $this->state(RoundState::class)->next();

        if ($next_round->status === 'upcoming') {
            $game->nextRound()->roundModel()->start();
        }
    }
}
