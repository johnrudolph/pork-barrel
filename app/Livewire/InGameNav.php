<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;

class InGameNav extends Component
{
    public function moneyHistory()
    {
        return $this->player->state()->money_history;
    }

    public function scores()
    {
        return $this->game->players->map(fn ($p) => [
            'industry' => $p->state()->industry,
            'player_id' => $p->id,
            'money' => $p->state()->availableMoney(),
        ])->sortByDesc('money');
    }

    public function perks()
    {
        return $this->player->state()->perks;
    }

    public Game $game;

    public Player $player;

    public Round $round;

    public ?Round $previous_round;

    public string $round_template;

    public function mount(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
        $this->round_template = $game->currentRound()->state()->round_template;
        $this->round = $game->currentRound();
        $this->previous_round = $game->currentRound()->previous();
    }

    public function setPreviousRound(Round $round)
    {
        $this->previous_round = $round;
    }

    public function render()
    {
        return view('livewire.in-game-nav');
    }
}
