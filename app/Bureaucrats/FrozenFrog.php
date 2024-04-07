<?php

namespace App\Bureaucrats;

use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerMoneyFrozen;
use App\Events\PlayerMoneyUnfrozen;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class FrozenFrog extends Bureaucrat
{
    const NAME = 'Frozen Frog';

    const SLUG = 'frozen-frog';

    const SHORT_DESCRIPTION = 'Freeze half of the assets of one Player for one round.';

    const DIALOG = "It's going to be a cold winter for whomever I investigate next round.";

    const EFFECT = 'Choose a Player. At the end of this round, I will freeze half of their money, and it will not be available next round.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_round_ended'];

    public static function suitability(RoundConstructor $constructor): int
    {
        if ($constructor->stageOfGame() === 'final-round') {
            return 0;
        }

        return 1;
    }

    public static function options(Round $round, Player $player)
    {
        return [
            'player' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->name])
                    ->toArray(),
                'label' => 'Player',
                'placeholder' => 'Select a Player',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        PlayerMoneyFrozen::fire(
            player_id: (int) $offer->data['player'],
            round_id: $round->id,
            amount: (int) round(PlayerState::load($offer->data['player'])->availableMoney() / 2),
            activity_feed_description: 'Half of your assets have been frozen. You will receive them back at the end of the next round.',
        );

        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer_id: $offer->id,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        PlayerMoneyUnfrozen::fire(
            player_id: (int) $original_offer->data['player'],
            round_id: $round->id,
            amount: PlayerState::load($original_offer->data['player'])->money_frozen,
            activity_feed_description: 'Your assets have been unfrozen. You now have access to all of your money.',
        );
    }
}
