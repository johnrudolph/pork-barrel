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

class ForecastFox extends Bureaucrat
{
    const NAME = 'Forecast Fox';

    const SLUG = 'forecast-fox';

    const SHORT_DESCRIPTION = "Guess which Player will have the most money, and get compensation if you're right.";

    const DIALOG = 'We need help seeing where the economy is headed.';

    const EFFECT = 'Select an Player. If that Player has the most money after this round, I will reward you with 7 money.';

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
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->name])
                    ->toArray(),
                'label' => 'Player',
                'placeholder' => 'Select a Player',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleEffectAfterEndOfRound(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $max_money = $round->game()->playerStates()
            ->max(fn ($p) => $p->availableMoney());

        $guess = PlayerState::load($offer->data['player']);

        if ($guess->availableMoney() === $max_money) {
            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 7,
                activity_feed_description: 'You correctly predicted that '.$guess->name.' would have the most money.',
                type: MoneyLogEntry::TYPE_BUREAUCRAT_REWARD,
            );
        }
    }
}
