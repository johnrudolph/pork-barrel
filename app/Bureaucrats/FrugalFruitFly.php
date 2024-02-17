<?php

namespace App\Bureaucrats;

use App\Events\OfferAmountModified;
use App\Events\PlayerGainedPerk;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class FrugalFruitFly extends Bureaucrat
{
    const NAME = 'Frugal Fruit Fly';

    const SLUG = 'frugal-Fruit-fly';

    const SHORT_DESCRIPTION = 'Only spend what is necessary when you win an auction.';

    const EFFECT = 'For the rest of the game, when you have the highest offer on an auction, your offer will be reduced as low as possible while still winning the auction.';

    const DIALOG = 'Wasteful spending is for the other party.';

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

            $player_offer = $all_offers_for_b
                ->filter(fn ($o) => $o->player_id === $player->id)->first();

            $player_has_top_offer = $offers_with_top_offer
                ->filter(fn ($o) => $o->player_id === $player->id)
                ->count() > 0;

            if (! $player_has_top_offer) {
                return;
            }

            $top_offer_is_tied = $offers_with_top_offer->count() > 1;

            if ($top_offer_is_tied) {
                return;
            }

            $there_are_multiple_offers = $all_offers_for_b->count() > 1;

            if (! $there_are_multiple_offers) {
                OfferAmountModified::fire(
                    player_id: $player->id,
                    round_id: $round->id,
                    offer_id: $player_offer->id,
                    amount_modified: 1 - $top_offer_amount,
                );

                return;
            }

            $second_highest_offer_amount = $all_offers_for_b
                ->reject(fn ($o) => $o->player_id === $player->id)
                ->max(fn ($o) => $o->netOffer());

            OfferAmountModified::fire(
                player_id: $player->id,
                round_id: $round->id,
                offer_id: $player_offer->id,
                amount_modified: 1 - $top_offer_amount + $second_highest_offer_amount,
            );
        });
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest bid for the Tied Hog. You will now win every tied auction for the rest of the game.';
    }
}
