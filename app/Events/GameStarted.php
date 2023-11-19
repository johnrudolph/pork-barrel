<?php

namespace App\Events;

use App\Models\Round;
use App\States\GameState;
use App\States\RoundState;
use Glhd\Bits\Snowflake;
use Illuminate\Support\Collection;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

#[AppliesToState(GameState::class)]
#[AppliesToState(RoundState::class)]
class GameStarted extends Event
{
	public function __construct(
		public int $game_id,
		public ?array $round_ids = null,
	) {
		$this->round_ids ??= Collection::times(8, fn() => Snowflake::make()->id())->values()->all();
	}
	
	public function applyToGame(GameState $state)
	{
		$state->status = 'in-progress';
	}
	
	public function applyToRound(RoundState $state)
	{
		$state->bureaucrats = collect();
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
