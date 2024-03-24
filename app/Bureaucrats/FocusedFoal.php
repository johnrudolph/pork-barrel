<?php

namespace App\Bureaucrats;

use App\Events\OfferAmountModified;
use App\Events\PlayerGainedPerk;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class FocusedFoal extends Bureaucrat
{
    const NAME = 'Focused Foal';

    const SLUG = 'focused-foal';

    const SHORT_DESCRIPTION = 'Add 5 money to your offer if it is the only one you make that round.';

    const EFFECT = 'For the rest of the game, if you only make 1 offer in a round, 5 money will be added to that offer.';

    const DIALOG = 'Multitasking is a myth.';

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
        $player_offers = $round->offers()
            ->filter(fn ($o) => $o->player_id === $player->id);

        if ($player_offers->count() !== 1) {
            return;
        }

        OfferAmountModified::fire(
            player_id: $player->id,
            round_id: $round->id,
            offer_id: $player_offers->first()->id,
            amount_modified: 5,
            modifier_description: '+5 from Focused Foal perk',
            is_charged_to_player: false,
        );
    }
}
