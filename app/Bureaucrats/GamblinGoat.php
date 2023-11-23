<?php

namespace App\Bureaucrats;

use App\Events\PlayerReceivedMoney;

class GamblinGoat extends Bureaucrat
{
    const NAME = "Gamblin' Goat";

    const SLUG = 'gamblin-goat';

    const SHORT_DESCRIPTION = 'Get a random return of money.';

    const DIALOG = "I've got a hair-brained scheme in the works. No promises, but I think it'll pay off big time.";

    const EFFECT = 'Get a random return of 1-10 money.';

    public static function resolveAtEndOfRoundFor(int $player_id, int $round_id, array $data = null)
    {
        PlayerReceivedMoney::fire(
            player_id: $player_id,
            round_id: $round_id,
            amount: rand(1, 10),
            activity_feed_description: "The Gamlin' Goat's scheme paid off!"
        );
    }
}
