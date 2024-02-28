<?php

namespace App\Events;

use App\DTOs\MoneyLogEntry;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerMoneyFrozen extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $activity_feed_description;

    public int $amount;

    public function apply(PlayerState $state)
    {
        $state->money_frozen += $this->amount;

        $state->money_history->push(new MoneyLogEntry(
            player_id: $this->player_id,
            round_id: $this->round_id,
            round_number: $state->game()->round_ids->search($this->round_id) + 1,
            amount: -$this->amount,
            description: $this->activity_feed_description,
            type: MoneyLogEntry::TYPE_FREEZE,
            balance: $state->availableMoney() - $this->amount,
        ));
    }
}
