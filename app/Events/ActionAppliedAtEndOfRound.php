<?php

namespace App\Events;

use App\DTOs\MoneyLogEntry;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionAppliedAtEndOfRound extends Event
{
    public int $round_id;

    public int $player_id;

    #[StateId(OfferState::class)]
    public int $offer_id;

    public function applyToOffer(OfferState $state)
    {
        //
    }

    public function handle()
    {
        $offer = $this->state(OfferState::class);

        $this->state(OfferState::class)->bureaucrat::handleOnRoundEnd(
            PlayerState::load($this->player_id),
            RoundState::load($this->round_id),
            $offer,
        );

        PlayerSpentMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            activity_feed_description: $offer->bureaucrat::activityFeedDescription(
                RoundState::load($this->round_id),
                $offer,
            ),
            amount: $offer->amount_offered,
            type: MoneyLogEntry::TYPE_WIN_AUCTION,
        );
    }
}
