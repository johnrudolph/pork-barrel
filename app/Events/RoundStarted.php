<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\States\GameState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public array $bureaucrats;

    public string $round_modifier;

    // @todo: validate that this is possible and good
    public function applyToRoundState(RoundState $state)
    {
        $state->status = 'auction';
        collect($this->bureaucrats)->each(fn ($b) => $state->bureaucrats->push($b));
        $state->round_modifier = $this->round_modifier;
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number += 1;
    }

    public function handle()
    {
        $this->state(RoundState::class)->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => $o->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_round_started'])
            ->each(fn ($o) => $o->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                $o,
            ));

        collect($this->state(RoundState::class)->game()->players)->each(fn ($player_id) => PlayerReceivedMoney::fire(
            player_id: $player_id,
            round_id: $this->round_id,
            amount: PlayerState::load($player_id)->income,
            activity_feed_description: 'Received income',
        )
        );

    }
}
