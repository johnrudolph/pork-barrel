<?php

namespace App\Bureaucrats;

use App\States\PlayerState;
use App\States\RoundState;
use App\DTOs\OfferDTO;
use App\Events\PlayerIncomeChanged;

class TaxTurkey extends Bureaucrat
{
    const NAME = 'Tax Turkey';

    const SLUG = 'tax-turkey';

    const SHORT_DESCRIPTION = 'Permanently reduce the income of another industry.';

    const DIALOG = 'There are only two things certain in life: death and taxes.';

    const EFFECT = 'Choose another industry to increase their taxes. Their income will permanently decrease by 1.';

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        PlayerIncomeChanged::fire(
            player_id: $offer->data['player'],
            round_id: $round->id,
            amount: -1,
            activity_feed_description: "You were taxed by the Tax Turkey."
        );
    }
}
