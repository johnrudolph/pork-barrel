<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class PonziPony extends Bureaucrat
{
    const NAME = 'Ponzi Pony';

    const SLUG = 'ponzi-pony';

    const SHORT_DESCRIPTION = "If you don't have the highest offer, receive the amount you offered.";

    const DIALOG = "Trust me, this is a sure thing. Just don't offer me more than the others.";

    const EFFECT = 'If you have the highest offer, you will lose your offer. Otherwise, you will pay nothing, and receive the amount you offered.';

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        $offers_for_pony = $round->offers()
            ->filter(fn ($o) => $o->bureaucrat === static::class);

        $top_offer = $offers_for_pony
            ->max(fn ($o) => $o->netOffer());

        $offers_for_pony
            ->filter(fn ($o) => $o->netOffer() < $top_offer)
            ->each(fn ($o) => PlayerReceivedMoney::fire(
                player_id: $o->player_id,
                round_id: $round->id,
                amount: $o->netOffer(),
                activity_feed_description: 'You did not have the highest offer for Ponzi Pony, and you got a return of your offer.',
                type: MoneyLogEntry::TYPE_AWARD,
            ));
    }
}
