<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\RoundState;
use App\States\PlayerState;
use Illuminate\Support\Collection;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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
        $state->status = 'in-progress';
        $state->phase = 'auction';
        collect($this->bureaucrats)->each(fn($b) => $state->bureaucrats->push($b));
        $state->round_modifier = $this->round_modifier;
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number += 1;
    }

    public function handle()
    {
        // $delete_this = RoundModifierAppliedAtBeginningOfRound::fire(
        //     round_id: $this->round_id,
        //     round_modifier: $this->round_modifier,
        // );

        // dump('round_modifier_applied', $delete_this->id);

        collect($this->state(RoundState::class)->game()->players)->each(fn ($player_id) => PlayerReceivedMoney::fire(
            player_id: $player_id,
            round_id: $this->round_id,
            amount: PlayerState::load($player_id)->income,
            activity_feed_description: 'Received income',
        )
        );

    }
}
