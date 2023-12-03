<?php

namespace App\Events;

use App\States\GameState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundStarted extends Event
{
    public function __construct(
        #[StateId(GameState::class)] public int $game_id,
        #[StateId(RoundState::class)] public int $round_id,
        public int $round_number,
        public $bureaucrats,
        public $round_modifier,
    ) {
    }

    public function applyToRoundState(RoundState $state)
    {
        $state->round_number = $this->round_number;
        $state->status = 'in-progress';
        $state->phase = 'auction';
        $state->bureaucrats = $this->bureaucrats;
        $state->game_id = $this->game_id;
        $state->round_modifier = $this->round_modifier;
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number = $this->round_number;
    }

    public function fired(RoundState $state)
    {
        RoundModifierAppliedAtBeginningOfRound::fire(
            round_id: $this->round_id,
            round_modifier: $this->round_modifier,
        );

        collect($state->gameState()->players)->each(fn ($player_id) => PlayerReceivedMoney::fire(
            player_id: $player_id,
            round_id: $this->round_id,
            amount: PlayerState::load($player_id)->income,
            activity_feed_description: 'Received income',
        )
        );

    }
}
