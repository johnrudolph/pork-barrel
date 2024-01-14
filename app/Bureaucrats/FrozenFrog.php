<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerMoneyFrozen;
use App\Events\PlayerMoneyUnfrozen;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\PlayerState;
use App\States\RoundState;

class FrozenFrog extends Bureaucrat
{
    const NAME = 'Frozen Frog';

    const SLUG = 'frozen-frog';

    const SHORT_DESCRIPTION = 'Freeze half of the assets of one industry for one round.';

    const DIALOG = "It's going to be a cold winter for whomever I investigate next round.";

    const EFFECT = 'Choose an industry. Next round, half of their money will not be available.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_ended';

    public static function suitability(RoundConstructor $constructor)
    {
        if ($constructor->stageOfGame() === 'late') {
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
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->industry])
                    ->toArray(),
                'label' => 'Industry',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
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
            offer: $offer,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        PlayerMoneyUnfrozen::fire(
            player_id: (int) $original_offer->data['player'],
            round_id: $round->id,
            amount: PlayerState::load($original_offer->data['player'])->money_frozen,
            activity_feed_description: 'Your assets have been unfrozen. You now have access to all of your money.',
        );
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest bid for the Minority Leader Mink. Next round, you will receive 10 money if you make no offers.';
    }
}
