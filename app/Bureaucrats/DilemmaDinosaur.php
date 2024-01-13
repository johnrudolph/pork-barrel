<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\Events\PlayerSpentMoney;
use App\States\RoundState;

class DilemmaDinosaur extends Bureaucrat
{
    const NAME = 'Dilemma Dinosaur';

    const SLUG = 'dilemma-dinosaur';

    const SHORT_DESCRIPTION = 'Get 2x your offer if no one else made an offer. Otherwise, lose 2x offer.';

    const DIALOG = "Some say I'm a prisoner of my own convoluted logic. I say I'm a just a dinosaur.";

    const EFFECT = 'If no one makes me an offer, everyone will receive 5 money. If one person makes me an offer, they will receive 2x their offer. If multiple people make me an offer, they will lose 2x their offer.';

    const HAS_WINNER = false;

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        $offers_for_dino = $round->offers
            ->filter(fn ($o) => $o->bureaucrat === static::class);

        if ($offers_for_dino->count() === 0) {
            $round->game()->players->each(fn ($p) => PlayerReceivedMoney::fire(
                player_id: $p,
                round_id: $round->id,
                amount: 5,
                activity_feed_description: 'No one made an offer for Dilemma Dinosaur, so everyone received 10 money.',
                type: MoneyLogEntry::TYPE_AWARD,
            ));
        } elseif ($offers_for_dino->count() === 1) {
            $offer = $offers_for_dino->first();

            PlayerReceivedMoney::fire(
                player_id: $offer->player_id,
                round_id: $round->id,
                amount: $offer->netOffer() * 3,
                activity_feed_description: 'You had the only offer for Dilemma Dinosaur, and you received your offer back, plus 2x your offer.',
                type: MoneyLogEntry::TYPE_AWARD,
            );
        } elseif ($offers_for_dino->count() > 1) {
            $offers_for_dino->filter(fn ($o) => $o->awarded)
                ->each(fn ($o) => PlayerSpentMoney::fire(
                    player_id: $o->player_id,
                    round_id: $round->id,
                    amount: $o->amount_offered,
                    activity_feed_description: 'There were multiple offers for Dilemma Dinosaur, and you lost 2x your offer.',
                    type: MoneyLogEntry::TYPE_PENALIZE,
                ));

            $offers_for_dino->reject(fn ($o) => $o->awarded)
                ->each(fn ($o) => PlayerSpentMoney::fire(
                    player_id: $o->player_id,
                    round_id: $round->id,
                    amount: $o->amount_offered * 2,
                    activity_feed_description: 'You did not have the highest offer for Dilemma Dinosaur, and you lost 2x your offer.',
                    type: MoneyLogEntry::TYPE_PENALIZE,
                ));
        }
    }
}
