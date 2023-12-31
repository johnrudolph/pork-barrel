<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\OfferAmountModified;
use App\States\PlayerState;
use App\States\RoundState;

class MajorityLeaderMare extends Bureaucrat
{
    const NAME = 'Majority Leader Mare';

    const SLUG = 'majority-leader-mare';

    const SHORT_DESCRIPTION = 'Add 1 money to each of your offers next round.';

    const DIALOG = "You can't get anything done around here without a majority. Scratch my back today, and I'll give you an in with the rest of the council tomorrow.";

    const EFFECT = 'After you submit your offers next round, 1 money will be added to each.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_auction_ended';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer: $offer,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        $round->offers
            ->filter(fn ($o) => $o->player_id === $original_offer->player_id)
            ->each(fn ($o) => OfferAmountModified::fire(
                player_id: $o->player_id,
                round_id: $round->id,
                offer: $o,
                amount_modified: 1,
            ));
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest bid for the Majority Leader Mare. Next round, 1 money will be added to each of your offers.';
    }
}
