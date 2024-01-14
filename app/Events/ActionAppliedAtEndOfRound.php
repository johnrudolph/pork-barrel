<?php

namespace App\Events;

use App\DTOs\MoneyLogEntry;
use App\DTOs\OfferDTO;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Event;

class ActionAppliedAtEndOfRound extends Event
{
    public int $round_id;

    public int $player_id;

    public OfferDTO $offer;

    public function handle()
    {
        $this->offer->bureaucrat::handleOnRoundEnd(
            PlayerState::load($this->player_id),
            RoundState::load($this->round_id),
            $this->offer,
        );

        PlayerSpentMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            activity_feed_description: $this->offer->bureaucrat::activityFeedDescription(
                RoundState::load($this->round_id),
                $this->offer,
            ),
            amount: $this->offer->amount_offered,
            type: MoneyLogEntry::TYPE_WIN_AUCTION,
        );
    }
}
