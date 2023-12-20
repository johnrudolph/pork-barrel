<?php

namespace App\Events;

use App\Models\MoneyLogEntry;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerSpentMoney extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $activity_feed_description;

    public int $amount;

    public function apply(PlayerState $state)
    {
        $state->money -= min($this->amount, $state->money);
    }

    public function handle()
    {
        MoneyLogEntry::create([
            'player_id' => $this->player_id,
            'round_id' => $this->round_id,
            'amount' => -$this->amount,
            'description' => $this->activity_feed_description,
        ]);
    }
}
