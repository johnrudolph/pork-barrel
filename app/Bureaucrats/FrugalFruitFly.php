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

    const EFFECT = 'At the end of each auction, if you overpaid for any bureaucrat that you won, I will modify your offer so that it is as low as possible while still beating your opponents (does not apply to Treasury Chicken).';

    const DIALOG = 'Wasteful spending is for the other party.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_auction_ended'];

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
        $round->bureaucrats
            ->filter(fn ($b) => $b::HAS_WINNER)
            ->each(function ($b) use ($player, $round) {
                $all_offers_for_b = $round->offers()
                    ->filter(fn ($o) => $o->bureaucrat === $b);

                $top_offer_amount = $all_offers_for_b
                    ->max(fn ($o) => $o->netOffer());

                $offers_with_top_offer = $all_offers_for_b
                    ->filter(fn ($o) => $o->netOffer() === $top_offer_amount);

                $player_offer = $all_offers_for_b
                    ->filter(fn ($o) => $o->player_id === $player->id)->first();

                if (! $player_offer) {
                    return;
                }

                $player_has_top_offer = $offers_with_top_offer
                    ->filter(fn ($o) => $o->player_id === $player->id)
                    ->count() > 0;

                if (! $player_has_top_offer) {
                    return;
                }

                if ($player_offer->netOffer() === 1) {
                    return;
                }

                $amount_to_pay = $player_offer->amountToChargePlayer();

                $second_highest_offer_amount = $all_offers_for_b
                    ->reject(fn ($o) => $o->player_id === $player->id)
                    ->max(fn ($o) => $o->netOffer());

                $target_amount = $second_highest_offer_amount + 1;

                if ($amount_to_pay <= $target_amount) {
                    return;
                }

                $amount_to_reimburse = $target_amount - $amount_to_pay;

                OfferAmountModified::fire(
                    player_id: $player->id,
                    round_id: $round->id,
                    offer_id: $player_offer->id,
                    amount_modified: $amount_to_reimburse,
                    modifier_description: 'Reduced by Frugal Fruit Fly',
                    is_charged_to_player: true,
                );
            });
    }
}
