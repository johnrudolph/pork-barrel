<?php

namespace App\Bureaucrats;

use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\OfferAmountModified;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class MajorityLeaderMare extends Bureaucrat
{
    const NAME = 'Majority Leader Mare';

    const SLUG = 'majority-leader-mare';

    const SHORT_DESCRIPTION = 'Add 1 money to each of your offers next round.';

    const DIALOG = "You can't get anything done around here without a majority. Scratch my back today, and I'll give you an in with the rest of the council tomorrow.";

    const EFFECT = 'After you submit your offers next round, I will add 1 money to each.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_awaiting_results'];

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'final-round'
            ? 0
            : 1;
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer_id: $offer->id,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        $round->offers()
            ->filter(fn ($o) => $o->player_id === $original_offer->player_id)
            ->each(fn ($o) => OfferAmountModified::fire(
                player_id: $o->player_id,
                round_id: $round->id,
                offer_id: $o->id,
                amount_modified: 1,
                modifier_description: '+1 from Majority Leader Mare',
                is_charged_to_player: false,
            ));
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest bid for the Majority Leader Mare. Next round, 1 money will be added to each of your offers.';
    }
}
