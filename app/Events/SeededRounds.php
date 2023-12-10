<?php

namespace App\Events;

use App\Models\Round;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;
use Thunk\Verbs\Event;

// @todo: maybe have RoundSeeded and fire it 8 times?
#[AppliesToState(GameState::class)]
class SeededRounds extends Event
{
    public function __construct(
        public int $game_id,
        public $round_ids,
    ) {
    }

    // @todo: write up a discussion in github to explain case for #AppliesToStateCollection

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
