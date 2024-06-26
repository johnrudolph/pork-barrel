<?php

namespace App\Bureaucrats;

use App\Events\OfferAmountModified;
use App\Events\PlayerGainedPerk;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class EqualityElk extends Bureaucrat
{
    const NAME = 'Equality Elk';

    const SLUG = 'equality-elk';

    const SHORT_DESCRIPTION = 'Get +10 for your offers if you are in last place.';

    const EFFECT = 'For the rest of the game, if you are alone in last place, I will add 10 money to each of your offers (does not apply to Treasury Chicken).';

    const DIALOG = 'Sometimes having the least is a real cool hand.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_awaiting_results'];

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
        $player_states = $round->game()->playerStates();

        $lowest_score = $player_states
            ->min(fn ($p) => $p->availableMoney());

        $there_is_a_tie_for_last_place = $player_states
            ->filter(fn ($p) => $p->availableMoney() === $lowest_score)
            ->count() > 1;

        if (
            $player->availableMoney() > $lowest_score
            || $there_is_a_tie_for_last_place
        ) {
            return;
        }

        $round->offers()
            ->filter(fn ($o) => $o->player_id === $player->id
                && $o->bureaucrat::HAS_WINNER
            )
            ->each(fn ($o) => OfferAmountModified::fire(
                player_id: $player->id,
                round_id: $round->id,
                offer_id: $o->id,
                amount_modified: 10,
                modifier_description: '+10 from Equality Elk perk',
                is_charged_to_player: false,
            ));
    }
}
