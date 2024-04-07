<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerGainedPerk;
use App\Events\PlayerLostPerk;
use App\Events\PlayerSpentMoney;
use App\Models\Headline;
use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class BearhugBrownBear extends Bureaucrat
{
    const NAME = 'Bearhug Brown Bear';

    const SLUG = 'bearhug-brown-bear';

    const SHORT_DESCRIPTION = 'Steal a Perk.';

    const DIALOG = "You see something you want, you go and take it. It's the Pork Barrel way.";

    const EFFECT = 'Select a Player. If they have Perks, I will steal a random one from them and give it to you. If they do not have any Perks, you will be fined 5 money.';

    public static function options(Round $round, Player $player)
    {
        return [
            'player' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->id => $p->state()->name])
                    ->toArray(),
                'label' => 'Bureaucrat',
                'placeholder' => 'Select a Player',
                'rules' => 'required',
            ],
        ];
    }

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'final-round'
            ? 0
            : 1;
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $target = PlayerState::load($offer->data['player']);

        if ($target->perks->count() === 0) {
            PlayerSpentMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 5,
                activity_feed_description: 'You attempted to give a bear hug to '.$target->name.', but they had no Perks to steal.',
                type: MoneyLogEntry::TYPE_PENALIZE,
            );

            return;
        }

        $perk = $target->perks->random();

        PlayerGainedPerk::fire(
            player_id: $player->id,
            round_id: $round->id,
            perk: $perk,
        );

        PlayerLostPerk::fire(
            player_id: (int) $offer->data['player'],
            round_id: $round->id,
            perk: $perk,
        );

        Headline::create([
            'round_id' => $round->id,
            'game_id' => $round->game()->id,
            'headline' => 'Brutal bear hug shocks markets',
            'description' => $player->name.' stole '.$perk::NAME.' from '.$target->name.'.',
        ]);
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        $hug_was_successful = PlayerState::load($offer->player_id)->perks->count() > 0;

        $target = PlayerState::load($offer->data['player']);

        return $hug_was_successful
            ? 'You gave a bear hug to '.$target->name.' and stole their Perk.'
            : 'You attempted to give a bear hug to '.$target->name.', but they had no Perks to steal.';
    }
}
