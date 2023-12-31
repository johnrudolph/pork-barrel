<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\PlayerAwardedBailout;
use App\States\PlayerState;
use App\States\RoundState;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, no one needs a stronger safety net than the rich.';

    const EFFECT = 'If you ever have 0 money after an auction, you will receive $10.';

    public static function rules(): array
    {
        return [
            'something' => 'string|required',
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        PlayerAwardedBailout::fire(
            player_id: $player->id,
            round_id: $round->id,
        );
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest bid for the Bailout Bunny. The next time you reach 0 money, you will receive 10 money.';
    }
}
