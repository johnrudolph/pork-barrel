<?php

namespace App\Bureaucrats;

use App\Models\Round;
use App\Models\Player;
use App\States\OfferState;
use App\States\RoundState;
use App\States\PlayerState;
use App\Events\PlayerIncomeChanged;
use App\RoundConstructor\RoundConstructor;

class TaxTurkey extends Bureaucrat
{
    const NAME = 'Tax Turkey';

    const SLUG = 'tax-turkey';

    const SHORT_DESCRIPTION = 'Permanently reduce the income of another industry.';

    const DIALOG = 'There are only two things certain in life: death and taxes.';

    const EFFECT = 'Choose another industry to increase their taxes. Their income will permanently decrease by 1.';

    public static function suitability(RoundConstructor $constructor): int
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
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->industry])
                    ->toArray(),
                'label' => 'Industry',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        PlayerIncomeChanged::fire(
            player_id: (int) $offer->data['player'],
            round_id: $round->id,
            amount: -1,
            activity_feed_description: 'You were taxed by the Tax Turkey.'
        );
    }
}
