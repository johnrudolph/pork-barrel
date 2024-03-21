<?php

namespace App\Livewire;

use App\Models\Game;
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

    public function headlines()
    {
        return $this->game->headlines;
    }

    public Game $game;

    public Player $player;

    public string $round_template;

    public function mount(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
        $this->round_template = $game->currentRound()->state()->round_template;
    }

    public function render()
    {
        return view('livewire.in-game-nav');
    }
}
