<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class OfferSubmitted extends Event
{
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public $bureaucrat;

    public $amount;

    public ?array $data = null;

    public function applyToRoundState(RoundState $state)
    {
        $state->offers->push([
            'player_id' => $this->player_id,
            'bureaucrat' => $this->bureaucrat,
            'original_amount' => $this->amount,
            'modified_amount' => $this->amount,
            'data' => $this->data,
        ]);
    }

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->actions_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($a) => $a['hook'] === $round::HOOKS['on_offer_submitted'])
            ->each(fn ($a) => $a['bureaucrat']::handleInFutureRound(
                PlayerState::load($a['player_id']),
                RoundState::load($this->round_id),
                $a['amount'],
                $a['data'],
            ));
    }
}
