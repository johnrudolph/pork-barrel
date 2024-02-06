<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\ActionEffectAppliedToFutureRound;
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

    const SHORT_DESCRIPTION = 'Receive the same amount of earnings as another Industry.';

    const DIALOG = 'And they say mimetics is just about violence...';

    const EFFECT = "Choose another Industry. At the start of the next round, you will receive that Industry's net earnings from this round (including their earnings and expenses, but not including their regular income).";

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_started';

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

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer_id: $offer->id,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        $target = PlayerState::load($original_offer->data['player']);

        $money_earned = $target
            ->money_history
            ->filter(fn ($entry) => $entry->type !== MoneyLogEntry::TYPE_INCOME
                && $entry->round_number === $round->game()->current_round_number - 1)
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
