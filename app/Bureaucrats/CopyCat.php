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

class CopyCat extends Bureaucrat
{
    const NAME = 'Copy Cat';

    const SLUG = 'copy-cat';

    const SHORT_DESCRIPTION = 'Copy the earnings of another Industry.';

    const DIALOG = 'And they say mimetics is just about violence...';

    const EFFECT = "Choose another Industry. After this round, I will give you money equal to that Industry's Bureaucrat awards from this round.";

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
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->industry])
                    ->toArray(),
                'label' => 'Bureaucrat',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleEffectAfterEndOfRound(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $target = PlayerState::load($offer->data['player']);

        $money_earned = $target
            ->money_history
            ->filter(fn ($entry) => $entry->type === MoneyLogEntry::TYPE_AWARD
                && $entry->round_number === $round->round_number
            )
            ->sum(fn ($entry) => $entry->amount);

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: $money_earned,
            activity_feed_description: 'You collected the earnings from '.$target->industry,
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
