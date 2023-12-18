<?php

namespace App\Bureaucrats;

use App\States\RoundState;
use App\States\PlayerState;
use App\Events\PlayerAwardedBailout;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, no one needs a stronger safety net than the rich.';

    const EFFECT = 'If you ever have 0 money after an auction, you will receive $10.';

    // @todo this is the concept for how we implement rules on the form
    public static function rules(): array
    {
        return [
            'something' => 'string|required',
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        PlayerAwardedBailout::fire(
            player_id: $player->id,
            round_id: $round->id,
        );
    }

    public static function activityFeedDescription(array $data = null)
    {
        return 'You had the highest bid for the Bailout Bunny. The next time you reach 0 money, you will receive 10 money.';
    }
}
