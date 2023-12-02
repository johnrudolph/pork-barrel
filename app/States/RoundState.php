<?php

namespace App\States;

use Thunk\Verbs\State;

class RoundState extends State
{
    public int $game_id;

    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public $bureaucrats;

    public $offers;

    public $blocked_actions;

    public $players_with_majority_leader_mare = [];

    public $players_with_minority_leader_mink = [];

    public $headline;

    public function gameState(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function actionsWonBy(int $player_id)
    {
        return collect($this->offers)
            ->filter(fn ($o) => $o['player_id'] === $player_id)
            ->filter(function ($offer) use ($player_id) {
                $top_offer = collect($this->offers)
                    ->filter(fn ($o) => $o['bureaucrat'] === $offer['bureaucrat'])
                    ->max(fn ($o) => $o['amount']);

                $player_with_mare_has_top_offer = collect($this->players_with_majority_leader_mare)
                    ->intersect(collect($this->offers)
                        ->filter(fn ($o) => $o['bureaucrat'] === $offer['bureaucrat'])
                        ->filter(fn ($o) => $o['amount'] === $top_offer)
                        ->pluck('player_id')
                    )->isNotEmpty();

                $top_offer = $player_with_mare_has_top_offer
                    ? $top_offer + 1
                    : $top_offer;

                $player_offer = collect($this->players_with_majority_leader_mare)->contains($offer['player_id'])
                    ? $offer['amount'] + 1
                    : $offer['amount'];

                return $player_offer >= $top_offer
                    && $player_offer > 0;
            });
    }
}
