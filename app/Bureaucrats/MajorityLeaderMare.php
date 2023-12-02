<?php

namespace App\Bureaucrats;

use App\Events\MajorityLeaderMareAppliedToNextRound;
use App\States\RoundState;
use App\States\PlayerState;

class MajorityLeaderMare extends Bureaucrat
{
    const NAME = 'Majority Leader Mare';

    const SLUG = 'majority-leader-mare';

    const SHORT_DESCRIPTION = 'Add 1 money to each of your offers next round.';

    const DIALOG = "You can't get anything done around here without a majority. Scratch my back today, and I'll give you an in with the rest of the council tomorrow.";

    const EFFECT = 'After you submit your offers next round, 1 money will be added to each.';

    public static function applyToRoundStateOnPurchase(RoundState $round_state, PlayerState $player_state, $amount, ?array $data = null)
    {
        MajorityLeaderMareAppliedToNextRound::fire(
            round_id: $round_state->gameState()->nextRoundId(),
            player_id: $player_state->id
        );
    }

    public static function activityFeedDescription(array $data = null)
    {
        return "You had the highest bid for the Majority Leader Mare. Next round, 1 money will be added to each of your offers.";
    }
}
