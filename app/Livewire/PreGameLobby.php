<?php

namespace App\Livewire;

use Livewire\Component;

class PreGameLobby extends Component
{
    public $game;

    public function startGame()
    {
        try {
            $this->game->start();
        } catch (\Throwable $th) {
            //
        }
    }

    public function render()
    {
        return view('livewire.pre-game-lobby');
    }
}
