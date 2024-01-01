<?php

namespace App\Events;

use App\Models\Round;
use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundSeeded extends Event
{
    public function __construct(
        #[StateId(GameState::class)] public int $game_id,
        #[StateId(RoundState::class)] public int $round_id,
        public int $round_number,
    ) {
    }

    public function applyToGame(GameState $state)
    {
        $state->round_ids->push($this->round_id);
    }

    public function applyToRound(RoundState $state)
    {
        $state->round_number = $this->round_number;
        $state->game_id = $this->game_id;
        $state->offers = collect();
        $state->bureaucrats = collect();
        $state->offers_from_previous_rounds_that_resolve_this_round = collect();
    }

    public function handle()
    {
        Round::create([
            'id' => $this->round_id,
            'game_id' => $this->game_id,
            'round_number' => $this->round_number,
        ]);
    }
}
