<?php

namespace App\Events;

use Thunk\Verbs\Event;
use Glhd\Bits\Snowflake;
use App\States\GameState;
use App\States\PlayerState;
use App\Events\PlayerReceivedMoney;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameEnded extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function applyToGame(GameState $state)
    {
        // @todo this is funny to have here.
        collect($state->players)->each(function ($player_id) use ($state) {
            $money_in_treasury = PlayerState::load($player_id)->money_in_treasury;

            if ($money_in_treasury > 0) {
                PlayerReceivedMoney::fire(
                    player_id: $player_id,
                    round_id: $state->current_round_id,
                    amount: intval($money_in_treasury * 1.25),
                    activity_feed_description: 'Received 25% return on money saved in treasury',
                );
            }
        });

        $state->status = 'ended';
    }

    public function fired()
    {
        SeededRounds::fire(
            game_id: $this->game_id,
            round_ids: collect(range(1, 8))->map(fn () => Snowflake::make()->id())->toArray(),
        );
    }
}
