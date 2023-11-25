<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;

class Headlines extends Component
{
    public Game $game;
    
    public $headline;

    public function mount()
    {
        $this->headline = $this->game->currentRound()->state()->headline;
    }

    public function render()
    {
        return view('livewire.headlines');
    }
}
