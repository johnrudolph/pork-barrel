<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionAwardedToPlayer extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public string $activity_feed_description;

    public string $bureaucrat;

    public int $amount;

    public ?array $data;

    public function applyToRound(RoundState $state)
    {
        $state->offers = $state->offers
            ->transform(function ($o) {
                if ($o->player_id === $this->player_id && $o->bureaucrat === $this->bureaucrat) {
                    $o->awarded = true;
                }

                return $o;
            });
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