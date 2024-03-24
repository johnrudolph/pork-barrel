<?php

namespace App\Bureaucrats;

use App\Events\InterestRateChanged;
use App\Models\Headline;
use App\Models\Player;
use App\Models\Round;
use App\States\RoundState;

class InterestInchworm extends Bureaucrat
{
    const NAME = 'Interest Inchworm';

    const SLUG = 'interest-inchworm';

    const SHORT_DESCRIPTION = 'Change the interest rate.';

    const DIALOG = "I'm really more of an economist than a politician. But who are we kidding?";

    const EFFECT = 'Increase or decrease the interest rate for all players. The interest rate determines how much return everyone gets from money put into the Treasury.';

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

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        $offers_for_inchworm = $round->offers()
            ->filter(fn ($o) => $o->bureaucrat === static::class);

        $top_offer = $offers_for_inchworm
            ->max(fn ($o) => $o->netOffer());

        $top_votes_for_increase = $offers_for_inchworm
            ->filter(fn ($o) => $o->netOffer() === $top_offer
                && $o->data['choice'] === 'increase'
            )
            ->count();

        $top_votes_for_decrease = $offers_for_inchworm
            ->filter(fn ($o) => $o->netOffer() === $top_offer
                && $o->data['choice'] === 'decrease'
            )
            ->count();

        if ($top_votes_for_increase === $top_votes_for_decrease) {
            Headline::create([
                'round_id' => $round->id,
                'game_id' => $round->game()->id,
                'headline' => 'Federal Reserve Stalemate',
                'description' => 'The powers that be are deadlocked over whether to increase or decrease interest rates. So they will do neither!',
            ]);

            return;
        }

        if ($top_votes_for_increase > $top_votes_for_decrease) {
            InterestRateChanged::fire(
                game_id: $round->game_id,
                round_id: $round->id,
                amount: 0.1
            );

            $new_rate = $round->game()->interest_rate * 100 + 10;

            Headline::create([
                'round_id' => $round->id,
                'game_id' => $round->game()->id,
                'headline' => 'Interest Rates Increased to '.$new_rate.'%.',
                'description' => 'In an effort to slow down inflation, the brilliant minds at the Federal Reserve have increased rates. Buy those bonds!',
            ]);

            return;
        }

        InterestRateChanged::fire(
            game_id: $round->game_id,
            round_id: $round->id,
            amount: -0.1
        );

        $new_rate = $round->game()->interest_rate * 100 - 10;

        Headline::create([
            'round_id' => $round->id,
            'game_id' => $round->game()->id,
            'headline' => 'Interest Rates Decreased to '.$new_rate.'%',
            'description' => 'Money is cheap, and we like it that way. Do not bother with bonds. Stimulate the economy and spend, spend, spend!',
        ]);
    }
}
