<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\PlayerState;
use App\Bureaucrats\Bureaucrat;
use App\RoundModifiers\RoundModifier;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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
            RoundStarted::fire(
                game_id: $this->game_id,
                round_id: $game->nextRound()->id,
                bureaucrats: Bureaucrat::all()->random(4)->toArray(),
                round_modifier: RoundModifier::all()->random(),
            );
        }
    }
}
