<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerSpentMoney;
use App\Models\Headline;
use App\Models\Player;
use App\Models\Round;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class Watchdog extends Bureaucrat
{
    const NAME = 'Watchdog';

    const SLUG = 'watchdog';

    const SHORT_DESCRIPTION = 'Guess who won a bureaucrat, and fine them.';

    const DIALOG = "Corruption is rampant around here. I'll sniff it out if it's the last thing I do.";

    const EFFECT = 'Select a player and a bureaucrat. If that player had the highest offer for that bureaucrat, I will fine them 5 money.';

    public static function options(Round $round, Player $player)
    {
        return [
            'bureaucrat' => [
                'type' => 'select',
                'options' => collect($round->state()->bureaucrats)
                    ->reject(fn ($b) => $b === static::class)
                    ->mapWithKeys(fn ($b) => [$b => $b::NAME]),
                'label' => 'Bureaucrat',
                'placeholder' => 'Select a bureaucrat',
                'rules' => 'required',
            ],
            'player' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->industry])
                    ->toArray(),
                'label' => 'Bureaucrat',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        if (
            $round->offers()->filter(fn ($o) => $o->awarded === true
                && $o->bureaucrat === $offer->bureaucrat
                && $o->player_id === $offer->player_id
            )
        ) {
            PlayerSpentMoney::fire(
                player_id: (int) $offer->data['player'],
                round_id: $round->id,
                amount: 5,
                activity_feed_description: 'Fined by the Watchdog. Bribery is not tolarated around these parts.',
                type: MoneyLogEntry::TYPE_PENALIZE,
            );

            $acused_industry = PlayerState::load((int) $offer->data['player'])->industry;

            Headline::create([
                'round_id' => $round->id,
                'game_id' => $round->game()->id,
                'headline' => $acused_industry.' caught bribing officials!',
                'description' => 'In a shocking discovery, the Watchdog has exposed the '.$acused_industry.' industry for bribing bureaucrat. They have been fined.',
            ]);
        }
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        $guess_was_correct = $state->offers()->filter(fn ($o) => $o->awarded === true
            && $o->bureaucrat === $offer->bureaucrat
            && $o->player_id === (int) $offer->data['player']
        )->count() > 0;

        $acused_industry = PlayerState::load($offer->data['player'])->industry;

        $acused_bureaucrat = $offer->data['bureaucrat']::NAME;

        return $guess_was_correct
            ? 'You had the highest bid for the Watchdog. You correctly accused '.$acused_industry.' of bribing '.$acused_bureaucrat.'. They have been fined 5 money.'
            : 'You had the highest bid for the Watchdog. You incorrectly accused '.$acused_industry.' of bribing '.$acused_bureaucrat.'.';
    }
}
