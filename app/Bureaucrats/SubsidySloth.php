<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerReceivedMoney;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class SubsidySloth extends Bureaucrat
{
    const NAME = 'Subsidy Sloth';

    const SLUG = 'subsidy-sloth';

    const SHORT_DESCRIPTION = "Guess which industry will have the least money, and give them money if you're right.";

    const DIALOG = 'Sometimes you have to give a little to get a little.';

    const EFFECT = 'Select an industry. If that industry has the least money at the beginning of the next round (before everyone receives income), they will receive 7 money.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_started';

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'final-round'
            ? 0
            : 1;
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

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer_id: $offer->id,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        $min_money = $round->game()->playerStates()
            ->min(fn ($p) => $p->availableMoney());

        $guess = PlayerState::load($original_offer->data['player']);

        if ($guess->availableMoney() === $min_money) {
            PlayerReceivedMoney::fire(
                player_id: $guess->id,
                round_id: $round->id,
                amount: 7,
                activity_feed_description: 'You received a subsidy from Subsidy Sloth.',
                type: MoneyLogEntry::TYPE_AWARD,
            );
        }
    }
}
