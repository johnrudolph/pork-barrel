<?php

namespace App\Bureaucrats;

use App\Events\OfferAmountModified;
use App\Events\PlayerGainedPerk;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class TiedHog extends Bureaucrat
{
    const NAME = 'Tied Hog';

    const SLUG = 'tied-hog';

    const SHORT_DESCRIPTION = 'Win every tied auction for the rest of the game.';

    const EFFECT = "For the rest of the game, if you are tied for the highest offer on an auction, each of your opponents' offers will be reduced by 1 and you will win outright.";

    const DIALOG = 'Just tipping the scales for the good guys.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_auction_ended';

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'first-round'
            ? 2
            : 0;
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        PlayerGainedPerk::fire(
            player_id: $player->id,
            round_id: $round->id,
            perk: static::class,
        );
    }

    public static function handlePerkInFutureRound(PlayerState $player, RoundState $round)
    {
        $round->bureaucrats->each(function ($b) use ($player, $round) {
            $all_offers_for_b = $round->offers()
                ->filter(fn ($o) => $o->bureaucrat === $b);

            $top_offer_amount = $all_offers_for_b
                ->max(fn ($o) => $o->netOffer());

            $offers_with_top_offer = $all_offers_for_b
                ->filter(fn ($o) => $o->netOffer() === $top_offer_amount);

            $player_has_top_offer = $offers_with_top_offer
                ->filter(fn ($o) => $o->player_id === $player->id)
                ->count() > 0;

            $top_offer_is_tied = $offers_with_top_offer->count() > 1;

            if ($player_has_top_offer && $top_offer_is_tied) {
                $offers_with_top_offer
                    ->reject(fn ($o) => $o->player_id === $player->id)
                    ->each(fn ($o) => OfferAmountModified::fire(
                        player_id: $o->player_id,
                        round_id: $round->id,
                        offer_id: $o->id,
                        amount_modified: -1,
                        modifier_description: "-1 from another player's Tied Hog perk",
                        is_charged_to_player: true,
                    ));
            }
        });
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest bid for the Tied Hog. You will now win every tied auction for the rest of the game.';
    }
}
