<?php

namespace App\Bureaucrats;

use App\Events\MinorityLeaderMinkAppliedToNextRound;
use App\States\PlayerState;
use App\States\RoundState;

class MinorityLeaderMink extends Bureaucrat
{
    const NAME = 'Minority Leader Mink';

    const SLUG = 'minority-leader-mink';

    const SHORT_DESCRIPTION = 'Earn a bonus for not making offers next round';

    const DIALOG = "The Majority will never lose if you keep bribing them. Boycott them next round and I'll make it worth your while.";

    const EFFECT = 'If you make no offers next round, you will earn 10 money.';

    const EFFECT_REQUIRES_DECISION = true;

    public static function applyToRoundStateAtEndOfRound(RoundState $round_state, PlayerState $player_state, $amount, array $data = null)
    {
        MinorityLeaderMinkAppliedToNextRound::fire(
            round_id: $round_state->gameState()->nextRoundId(),
            player_id: $player_state->id
        );
    }

    public static function activityFeedDescription(array $data = null)
    {
        return 'You had the highest bid for the Minority Leader Mink. Next round, you will receive 10 money if you make no offers.';
    }
}
