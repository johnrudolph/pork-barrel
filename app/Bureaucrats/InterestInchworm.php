<?php

namespace App\Bureaucrats;

use App\Events\InterestRateChanged;
use App\Models\Headline;
use App\Models\Player;
use App\Models\Round;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class InterestInchworm extends Bureaucrat
{
    const NAME = 'Interest Inchworm';

    const SLUG = 'interest-inchworm';

    const SHORT_DESCRIPTION = 'Change the interest rate.';

    const DIALOG = "I'm really more of an economist than a politician. But who are we kidding?";

    const EFFECT = 'I can increase or decrease the interest rate for all players. The interest rate determines how much return everyone gets from money put into the Treasury.';

    public static function options(Round $round, Player $player)
    {
        return [
            'choice' => [
                'type' => 'select',
                'options' => collect([
                    'increase' => 'Increase by 10%',
                    'decrease' => 'Decrease by 10%',
                ]),
                'label' => 'Choice',
                'placeholder' => 'Make your choice',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $amount = $offer->data['choice'] === 'increase'
            ? 0.1
            : -0.1;

        InterestRateChanged::fire(
            game_id: $round->game_id,
            round_id: $round->id,
            amount: $amount,
        );

        $headline = $offer->data['choice'] === 'increase'
            ? 'Interest Rates Increased to '.$round->game()->interest_rate.'%'
            : 'Interest Rates Decreased to '.$round->game()->interest_rate.'%';

        $description = $offer->data['choice'] === 'increase'
            ? 'Money is expensive, and we like it that way. Bonds are the safest investment of all. Save, save, save!'
            : 'Money is cheap, and we like it that way. Do not bother with bonds. Stimulate the economy and spend, spend, spend!';

        Headline::create([
            'round_id' => $round->id,
            'game_id' => $round->game()->id,
            'headline' => $headline,
            'description' => $description,
        ]);
    }
}
