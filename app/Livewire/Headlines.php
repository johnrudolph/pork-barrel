<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;

class Headlines extends Component
{
    public Game $game;

    public $headline;

    public $other_headlines;

    public function mount()
    {
        $this->headline = $this->game->currentRound()->state()->round_modifier;
        $this->other_headlines = $this->game->headlines;
    }

    public function render()
    {
        return view('livewire.headlines');
    }
}
