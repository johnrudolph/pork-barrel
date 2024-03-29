<?php

namespace App\Bureaucrats;

use App\Events\BureaucratWasBlocked;
use App\Models\Player;
use App\Models\Round;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class ObstructionOx extends Bureaucrat
{
    const NAME = 'Obstruction Ox';

    const SLUG = 'obstruction-ox';

    const SHORT_DESCRIPTION = "Cancel a bureaucrat's action.";

    const DIALOG = 'Obstructionism is the only way to not get things done in this town.';

    const EFFECT = 'Select a bureaucrat, and they will not accept any offers this round. No one will give them money, and no one will receive its effect.';

    const EFFECT_REQUIRES_DECISION = true;

    const SELECT_PROMPT = 'Select a bureaucrat';

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
        ];
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $b = $offer->data['bureaucrat'];

        BureaucratWasBlocked::fire(
            round_id: $round->id,
            bureaucrat: $b,
            headline: $b::NAME.' Ousted',
            description: 'The Obstructionist Ox blocked '.$b::NAME.' from taking action this round.'
        );
    }
}
