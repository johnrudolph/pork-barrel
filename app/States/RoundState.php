<?php

namespace App\States;

use Thunk\Verbs\State;
use App\States\GameState;

class RoundState extends State
{
    public int $game_id;

    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public $bureaucrats;

    public $offers;

    public $blocked_actions;

    public function gameState(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function actionsAvailableTo(int $player_id)
    {
        return collect($this->bureaucrats)
            ->filter(function ($b) use ($player_id) {
                $top_offer = collect($this->offers)
                    ->filter(fn ($o) => $o['bureaucrat'] === $b)
                    ->max(fn ($o) => $o['amount']);
                
                $player_offer = collect($this->offers)
                    ->filter(fn ($o) => $o['bureaucrat'] === $b && $o['player_id'] === $player_id)
                    ->max(fn ($o) => $o['amount']);

                return $top_offer === $player_offer
                    && $top_offer;
            });
    }
}
