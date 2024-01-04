<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerReceivedMoney;
use App\Models\Player;
use App\Models\Round;
use App\States\PlayerState;
use App\States\RoundState;

class ForecastFox extends Bureaucrat
{
    const NAME = 'Forecast Fox';

    const SLUG = 'forecast-fox';

    const SHORT_DESCRIPTION = "Guess which industry will have the most money, and get compensation if you're right.";

    const DIALOG = 'We need help seeing where the economy is headed.';

    const EFFECT = 'Select an industry. If that industry has the most money at the beginning of the next round (before everyone receives income), you will receive 7 money.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_started';

    public static function options(Round $round, Player $player)
    {
        return [
            'player' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->industry])
                    ->toArray(),
                'label' => 'Bureaucrat',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer: $offer,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        $max_money = $round->game()->playerStates()
            ->max(fn ($p) => $p->money);

        $guess = PlayerState::load($original_offer->data['player']);

        if ($guess->money === $max_money) {
            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 7,
                activity_feed_description: 'You correctly predicted that '.$guess->industry.' would have the most money.',
            );
        }
    }
}
