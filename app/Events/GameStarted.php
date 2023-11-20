<?php

namespace App\Events;

use App\Models\Round;
use Thunk\Verbs\Event;
use Glhd\Bits\Snowflake;
use App\States\GameState;
use App\States\RoundState;
use App\Events\SeededRounds;
use Illuminate\Support\Collection;
use Thunk\Verbs\Attributes\Autodiscovery\AppliesToState;

#[AppliesToState(GameState::class)]
// #[AppliesToState(RoundState::class)]
class GameStarted extends Event
{
	public function __construct(
		public int $game_id,
		// public ?array $round_ids = null,
	) {
		// $this->round_ids ??= Collection::times(8, fn() => Snowflake::make()->id())->values()->all();
	}
	
	public function applyToGame(GameState $state)
	{
		$state->status = 'in-progress';
	}
	
	public function fired()
	{
		SeededRounds::fire(
			game_id: $this->game_id, 
			round_ids: Collection::times(8, fn() => Snowflake::make()->id())->values()->all()
		);
	}
}
