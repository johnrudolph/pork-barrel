<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Events\PlayerReceivedMoney;
use App\Models\Headline;
use App\Models\Player;
use App\Models\Round;
use App\States\PlayerState;
use App\States\RoundState;

class MuckrakingMule extends Bureaucrat
{
    const NAME = 'Muckraing Mule';

    const SLUG = 'muckraking-mule';

    const SHORT_DESCRIPTION = 'Guess which player works for an industry, expose them, and earn 5 money.';

    const DIALOG = "It's time to expose the corporate lobbyists in this town. Help me expose a huge story.";

    const EFFECT = 'Select a player and an industry. If that player works for that industry, there will be a headline exposing them, and you will earn 5 money.';

    public static function options(Round $round, Player $player)
    {
        return [
            'player' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->id => $p->user->name])
                    ->toArray(),
                'label' => 'Player',
                'placeholder' => 'Select a player',
                'rules' => 'required',
            ],
            'industry' => [
                'type' => 'select',
                'options' => $round->game->players
                    ->reject(fn ($p) => $p->id === $player->id)
                    ->mapWithKeys(fn ($p) => [$p->state()->industry => $p->state()->industry])
                    ->toArray(),
                'label' => 'Industry',
                'placeholder' => 'Select an industry',
                'rules' => 'required',
            ],
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        if ($offer->data['industry'] === PlayerState::load($offer->data['player'])->industry) {
            $acusee = Player::find($offer->data['player']);

            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 5,
                activity_feed_description: "You exposed {$acusee->user->name} as a corporate lobbyist for {$acusee->state()->industry}.",
            );

            Headline::create([
                'round_id' => $round->id,
                'game_id' => $round->game()->id,
                'headline' => 'Muckraking Mule Exposes Lobbyist',
                'description' => "{$acusee->user->name} was exposed as a corporate lobbyist for {$acusee->state()->industry}.",
            ]);
        }
    }
}
