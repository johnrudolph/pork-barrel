<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
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

    const EFFECT = 'Select an industry. If that industry has the least money at the end of this round (before everyone receives income), I will give them 7 money.';

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

    public static function handleEffectAfterEndOfRound(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $min_money = $round->game()->playerStates()
            ->min(fn ($p) => $p->availableMoney());

        $guess = PlayerState::load($offer->data['player']);

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
