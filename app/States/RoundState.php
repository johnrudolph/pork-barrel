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

    public $headline;

    public function gameState(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function actionsWonBy(int $player_id)
    {
        return collect($this->offers)
            ->filter(function ($offer) use ($player_id) {
                $top_offer = collect($this->offers)
                    ->filter(fn ($o) => $o['bureaucrat'] === $offer['bureaucrat'])
                    ->max(fn ($o) => $o['amount']);

                return $offer['player_id'] === $player_id
                    && $offer['amount'] === $top_offer
                    && $offer['amount'] > 0;
            });
    }
}
