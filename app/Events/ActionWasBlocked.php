<?php

namespace App\Events;

use App\Models\Headline;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionWasBlocked extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public $bureaucrat;

    public $headline;

    public $description;

    public function applyToRoundState(RoundState $state)
    {
        $state->blocked_actions[] = $this->bureaucrat;
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
