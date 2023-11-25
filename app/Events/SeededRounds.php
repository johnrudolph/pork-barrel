<?php

namespace App\Events;

use App\Models\Round;
use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;
use Thunk\Verbs\Event;

#[AppliesToState(GameState::class)]
class SeededRounds extends Event
{
    public function __construct(
        public int $game_id,
        public $round_ids,
    ) {
    }

    public function applyToRound(RoundState $state)
    {
    	$state->bureaucrats = collect();
    }

    public function applyToGame(GameState $state)
    {
    	$state->rounds = $this->round_ids;
    }

    public function handle()
    {
        foreach ($this->round_ids as $index => $round_id) {
            Round::create([
                'id' => $round_id,
                'game_id' => $this->game_id,
                'round_number' => $index + 1,
            ]);
        }
    }
}
