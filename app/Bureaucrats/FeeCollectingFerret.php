<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerGainedPerk;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class FeeCollectingFerret extends Bureaucrat
{
    const NAME = 'Fee Collecting Ferret';

    const SLUG = 'fee-collecting-ferret';

    const SHORT_DESCRIPTION = 'When one of your offers is rejected, receive 1 money for each offer made by an opponent for the same Bureaucrat.';

    const DIALOG = 'A little something, you know, for the effort.';

    const EFFECT = 'For the rest of the game, when one of your offers is rejected, receive 1 money for each offer made by an opponent for the same Bureaucrat.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_ended';

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

            $player_offer = $all_offers_for_b
                ->filter(fn ($o) => $o->player_id === $player->id)
                ->first();

            if (! $player_offer) {
                return;
            }

            $player_won = $player_offer->awarded;

            if ($player_won || $player_offer === null) {
                return;
            }

            $number_of_other_offers = $all_offers_for_b
                ->count() - 1;

            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: $number_of_other_offers,
                activity_feed_description: 'You received fees for rejected offers.',
                type: MoneyLogEntry::TYPE_AWARD,
            );
        });
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest offer for the Fee Collecting Ferret. You now get compensated when you lose offers.';
    }
}
