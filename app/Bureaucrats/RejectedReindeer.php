<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerGainedPerk;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class RejectedReindeer extends Bureaucrat
{
    const NAME = 'Rejected Reindeer';

    const SLUG = 'rejected-reindeer';

    const SHORT_DESCRIPTION = 'If none of your offers are accepted, receive 4 money.';

    const EFFECT = 'For the rest of the game, if none of your offers are accepted in a round, I will give you 4 money.';

    const DIALOG = "Sometimes you fit right in, and sometimes you've got a bright red nose.";

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_round_ended'];

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
        $offers_awarded_to_player = $round->offers()
            ->filter(fn ($o) => $o->player_id === $player->id
                && $o->awarded
            );

        if ($offers_awarded_to_player->count() === 0) {
            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 4,
                activity_feed_description: 'None of your offers were accepted. You received 4 money.',
                type: MoneyLogEntry::TYPE_AWARD,
            );
        }
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest bid for the Tied Hog. You will now win every tied auction for the rest of the game.';
    }
}
