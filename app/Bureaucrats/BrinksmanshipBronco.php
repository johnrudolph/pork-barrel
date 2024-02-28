<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\Events\PlayerSpentMoney;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class BrinksmanshipBronco extends Bureaucrat
{
    const NAME = 'Brinksmanship Bronco';

    const SLUG = 'brinksmanship-bronco';

    const SHORT_DESCRIPTION = 'Receive all of the offers for this Bureaucrat.';

    const DIALOG = "Who's afraid of a game of chicken? Not me. Because I'm a horse.";

    const EFFECT = 'If you have the highest offer, you will keep all the lower offers made to this Bureaucrat. If multiple players offer the highest offer, they will split the earnings, rounded down.';

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $offers_for_bronco = $round->offers()
            ->filter(fn ($o) => $o->bureaucrat === static::class);

        $top_offer = $offers_for_bronco
            ->max(fn ($o) => $o->netOffer());

        $sum_offered = $offers_for_bronco
            ->reject(fn ($o) => $o->netOffer() === $top_offer)
            ->sum(fn ($o) => $o->netOffer());

        $number_of_winners = $offers_for_bronco
            ->filter(fn ($o) => $o->netOffer() === $offer->netOffer())
            ->count();

        return $offers_for_bronco->count() === 1
            ? PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 10,
                activity_feed_description: 'You were the only player to offer for Brinksmanship Bronco.',
                type: MoneyLogEntry::TYPE_AWARD,
            )
            : PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: intval($sum_offered / $number_of_winners),
                activity_feed_description: 'You received the all the offers for Brinksmanship Bronco.',
                type: MoneyLogEntry::TYPE_AWARD,
            );
    }

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        $offers_for_bronco = $round->offers()
            ->filter(fn ($o) => $o->bureaucrat === static::class);

        $winning_offer = $offers_for_bronco
            ->max(fn ($o) => $o->netOffer());

        $offers_for_bronco
            ->filter(fn ($o) => $o->netOffer() < $winning_offer)
            ->each(fn ($o) => PlayerSpentMoney::fire(
                player_id: $o->player_id,
                round_id: $round->id,
                amount: $o->amount_offered,
                activity_feed_description: 'You did not have the highest offer for Brinksmanship Bronco.',
                type: MoneyLogEntry::TYPE_PENALIZE,
            ));
    }
}
