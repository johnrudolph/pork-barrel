<?php

namespace App\Bureaucrats;

use App\Events\PlayerIncomeChanged;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class TaxTurkey extends Bureaucrat
{
    const NAME = 'Tax Turkey';

    const SLUG = 'tax-turkey';

    const SHORT_DESCRIPTION = 'Permanently reduce the income of another Player.';

    const DIALOG = 'There are only two things certain in life: death and taxes.';

    const EFFECT = 'Choose another Player to increase their taxes. Their income will permanently decrease by 1.';

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
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->name])
                    ->toArray(),
                'label' => 'Player',
                'placeholder' => 'Select a Player',
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
