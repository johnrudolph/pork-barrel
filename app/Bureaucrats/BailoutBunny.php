<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerGainedPerk;
use App\Events\PlayerReceivedMoney;
use App\Models\Headline;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, no one needs a stronger safety net than the rich.';

    const EFFECT = 'If you ever reach 0 money in any future round, you will receive 10 money.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_spent_money';

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
        if ($player->availableMoney() === 0) {
            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 10,
                activity_feed_description: 'You received a bailout. No one needs a stronger safety net than you.',
                type: MoneyLogEntry::TYPE_AWARD,
            );

            Headline::create([
                'round_id' => $round->id,
                'game_id' => $round->game()->id,
                'headline' => $player->industry.' bailed out!',
                'description' => 'After hitting rock bottom, the '.$player->industry." industry has received a bailout. In order to have a just society, it's important that the mega-rich cannot fail. This corporate welfare will help this industry pick themselves up by their own bootstraps, and then put that boot right back on your neck.",
            ]);
        }
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest bid for the Bailout Bunny. Every time you reach 0 money, you will receive 10 money.';
    }
}
