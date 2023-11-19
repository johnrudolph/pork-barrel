<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\DTOs\ActionDTO;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class ActionsMadeAvailableToPlayers extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function apply(RoundState $state)
    {
        $state->actions = collect($state->gameState()->players)
            ->mapWithKeys(fn ($player_id) =>
                collect($state->auction_winners)
                    ->filter(fn ($action) => in_array($player_id, $action['winning_player_ids']))
                    ->keys()
                    ->map(fn ($action) => new ActionDTO($player_id, $this->round_id, $action)));
                    // ->map(fn ($bureaucrat) => [
                    //     'player_id' => $player_id,
                    //     'class' => $bureaucrat,
                    //     'requires_decision' => $bureaucrat::EFFECT_REQUIRES_DECISION,
                    //     'options' => null,
                    // ])
            // ->toArray();
    }
}
