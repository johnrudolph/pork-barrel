<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class ActionAwardedToPlayer extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public string $activity_feed_description;

    public string $bureaucrat;

    public int $amount;

    public ?array $data = null;

    public function applyToRound(RoundState $state)
    {
        $state->actions_awarded->push([
            'player_id' => $this->player_id,
            'bureaucrat' => $this->bureaucrat,
            'amount' => $this->amount,
            'data' => $this->data,
        ]);
    }

    public function handle()
    {
        $this->bureaucrat::handleOnAwarded(
            $this->state(PlayerState::class),
            $this->state(RoundState::class),
            $this->amount, 
            $this->data
        );

        PlayerSpentMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            activity_feed_description: $this->activity_feed_description,
            amount: $this->amount,
        );
    }
}
