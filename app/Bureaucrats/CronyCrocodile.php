<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\PlayerIncomeChanged;
use App\States\PlayerState;
use App\States\RoundState;

class CronyCrocodile extends Bureaucrat
{
    const NAME = 'Crony Crocodile';

    const SLUG = 'crony-crocodile';

    const SHORT_DESCRIPTION = 'Permanently increase your income.';

    const DIALOG = "I am not a crook. I'm just a crocodile.";

    const EFFECT = 'Permanently increase your income by 1.';

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        PlayerIncomeChanged::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: 1,
            activity_feed_description: 'Your income was increased by the Crony Crocodile.'
        );
    }
}
