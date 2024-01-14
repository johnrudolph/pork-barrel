<?php

namespace App\Events;

use App\DTOs\MoneyLogEntry;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerPutMoneyInTreasury extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $activity_feed_description;

    public int $amount;

    public function apply(PlayerState $state)
    {
        $state->money_in_treasury += $this->amount;
    }

    public function handle()
    {
        PlayerSpentMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            activity_feed_description: $this->activity_feed_description,
            round_number: $this->state(PlayerState::class)->game()->round_ids->search($this->round_id) + 1,
            amount: $this->amount,
            type: MoneyLogEntry::TYPE_TREASURY,
        );
    }
}
