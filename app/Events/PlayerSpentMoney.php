<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\DTOs\MoneyLogEntry;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerSpentMoney extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $activity_feed_description;

    public int $amount;

    public string $type;

    public function apply(PlayerState $state)
    {
        $amount_zeroed = min($this->amount, $state->availableMoney());

        $state->money_history->push(new MoneyLogEntry(
            player_id: $this->player_id,
            round_id: $this->round_id,
            round_number: $state->game()->round_ids->search($this->round_id) + 1,
            amount: -$amount_zeroed,
            description: $this->activity_feed_description,
            type: $this->type,
            balance: $state->availableMoney() - $amount_zeroed,
        ));
    }

    public function fired()
    {
        $this->state(PlayerState::class)->perks
            ->filter(fn ($perk) => $perk::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_spent_money'])
            ->each(fn ($perk) => $perk::handlePerkInFutureRound(
                PlayerState::load($this->player_id),
                RoundState::load($this->round_id)
            ));
    }
}
