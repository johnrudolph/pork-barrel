<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class ConsolationCow extends Bureaucrat
{
    const NAME = 'Consolation Cow';

    const SLUG = 'consolation-cow';

    const SHORT_DESCRIPTION = 'Receive money for all of your rejected offers.';

    const DIALOG = 'There, there. Just because you lost everything does not mean you should suffer.';

    const EFFECT = 'Receive 1 money for each offer you made that was not awarded.';

    public static function suitability(RoundConstructor $constructor): int
    {
        if ($constructor->stageOfGame() === 'final-round') {
            return 2;
        }

        return 0;
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $consolation = $round->game()->rounds()
            ->reduce(function (int $total, $round) {
                return $total + $round->offers()
                    ->filter(fn ($o) => $o->bureaucrat::HAS_WINNER
                        && ! $o->awarded
                    )
                    ->count();
            }, 0);

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: $consolation,
            activity_feed_description: 'Consolation funds from the Cow',
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
