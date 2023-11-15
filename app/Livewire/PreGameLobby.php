<?php

namespace App\Livewire;

use Livewire\Component;

class PreGameLobby extends Component
{
    public $game;

    public function startGame()
    {
        $this->game->start();
    }

    public function render()
    {
        return view('livewire.pre-game-lobby');
    }
}
