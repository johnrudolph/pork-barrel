<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\DTOs\MoneyLogEntry;
use App\Models\Round;
use App\States\GameState;
use App\States\OfferState;
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

    public string $round_template;

    // @todo: validate that this is possible and good
    public function applyToRoundState(RoundState $state)
    {
        $state->status = 'auction';
        collect($this->bureaucrats)->each(fn ($b) => $state->bureaucrats->push($b));
        $state->round_template = $this->round_template;
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number = $this->state(RoundState::class)->round_number;
    }

    public function fired()
    {
        $this->round_template::handleOnRoundStart($this->state(RoundState::class));

        $this->state(RoundState::class)->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => OfferState::load($o)->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_round_started'])
            ->each(fn ($o) => OfferState::load($o)->bureaucrat::handleInFutureRound(
                PlayerState::load(OfferState::load($o)->player_id),
                RoundState::load($this->round_id),
                OfferState::load($o),
            ));

        collect($this->state(RoundState::class)->game()->players)->each(fn ($player_id) => PlayerReceivedMoney::fire(
            player_id: $player_id,
            round_id: $this->round_id,
            amount: PlayerState::load($player_id)->income,
            activity_feed_description: 'Received income',
            type: MoneyLogEntry::TYPE_INCOME,
        )
        );
    }

    public function handle()
    {
        Round::find($this->round_id)->update(['status' => 'auction']);
    }
}
