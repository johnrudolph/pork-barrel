<?php

namespace App\Events;

use App\Models\Headline;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class BureaucratWasBlocked extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public $bureaucrat;

    public $headline;

    public $description;

    public function applyToRoundState(RoundState $state)
    {
        $state->blocked_bureaucrats->push($this->bureaucrat);

        $state->offers()->filter(fn ($o) => $o->bureaucrat === $this->bureaucrat)
            ->each(fn ($o) => OfferWasBlocked::fire(
                offer_id: $o->id,
                round_id: $this->round_id,
            ));
    }

    public function handle()
    {
        Headline::create([
            'round_id' => $this->round_id,
            'game_id' => $this->state(RoundState::class)->game()->id,
            'headline' => $this->headline,
            'description' => $this->description,
        ]);
    }
}
