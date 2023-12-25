<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class PreGameLobby extends Component
{
    public Game $game;

    #[Computed]
    public function players()
    {
        return $this->game->players->map(fn ($p) => $p->user->name);
    }

    #[On('echo:games.{game.id},GameUpdated')]
    public function gameUpdated()
    {
        // $this->initializeProperties();
    }

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
