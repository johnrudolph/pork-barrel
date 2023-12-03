<?php

namespace App\Bureaucrats;

use App\States\PlayerState;
use App\States\RoundState;

class TreasuryChicken extends Bureaucrat
{
    const NAME = 'Treasury Chicken';

    const SLUG = 'treasury-chicken';

    const SHORT_DESCRIPTION = 'Buy a treasury bond, and earn interest later.';

    const DIALOG = 'A penny saved is a penny earned.';

    const EFFECT = 'The winning bidder will spend the money now, and at the end of the game will receive their money back with 25% interest (rounded down).';

    public static function applyToPlayerStateAtEndOfRound(PlayerState $player_state, RoundState $round_state, $amount, array $data = null)
    {
        $player_state->money_in_treasury += $amount;
    }

    public static function activityFeedDescription(array $data = null)
    {
        return 'You had the highest bid for the Treasury Chicken. Your money is now tied up in a treasury bond, and you will get it back with 25% interest at the end of the game.';
    }
}
