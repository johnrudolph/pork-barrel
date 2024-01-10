<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\PlayerState;
use App\States\RoundState;

class Bureaucrat
{
    const NAME = '';

    const SLUG = '';

    const SHORT_DESCRIPTION = '';

    const DIALOG = '';

    const EFFECT = '';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = '';

    const HAS_WINNER = true;

    const HOOKS = [
        'on_round_started' => 'on_round_started',
        'on_offer_submitted' => 'on_offer_submitted',
        'on_auction_ended' => 'on_auction_ended',
        'on_round_ended' => 'on_round_ended',
    ];

    public static function all()
    {
        return collect([
            BailoutBunny::class,
            BrinksmanshipBronco::class,
            DilemmaDinosaur::class,
            ForecastFox::class,
            FrozenFrog::class,
            GamblinGoat::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            MuckrakingMule::class,
            ObstructionOx::class,
            SubsidySloth::class,
            TaxTurkey::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);
    }

    public static function suitability(RoundConstructor $constructor)
    {
        return 1;
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        // only use this if it will modify offers before they are resolved end of round
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        // this is the standard handler for most bureaucrats, and applies to each winner
    }

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        // this can handle effects that don't just apply to the winners
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        // this gets called when the effect happens in a future round, and needs to be paired with static::HOOK_TO_APPLY_IN_FUTURE_ROUND
    }

    public static function handleOnGameEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        // this only gets called when the game ends
    }

    public static function options(Round $round, Player $player)
    {
        // this is used to show options and rules for livewire components
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest offer for '.static::NAME;
    }
}
