<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class PreGameLobby extends Component
{
    public $game;

    public array $players;

    #[On('echo:games.{game.id},GameUpdated')]
    public function showNumberOfOffers()
    {
        $this->initializeProperties();
    }

    public function mount()
    {
        $this->initializeProperties();
    }

    public function initializeProperties()
    {
        $this->players = $this->game->players
            ->map(fn ($p) => $p->user->name)
            ->toArray();
    }

    #[On('echo:games.{game.id},GameUpdated')]
    public function gameStarted()
    {
        $this->dispatch('game-started');
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
