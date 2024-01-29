<?php

namespace App\Events;

use App\DTOs\OfferDTO;
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

    public OfferDTO $offer;

    public function applyToRound(RoundState $state)
    {
        $state->offers = $state->offers
            ->transform(function ($o) {
                if ($o->player_id === $this->player_id && $o->bureaucrat === $this->offer->bureaucrat) {
                    $o->awarded = true;
                }

                return $o;
            });
    }

    public function applyToPlayer(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        $this->offer->bureaucrat::handleOnAwarded(
            $this->state(PlayerState::class),
            $this->state(RoundState::class),
            $this->offer,
        );
    }
}
