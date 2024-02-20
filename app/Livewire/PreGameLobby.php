<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

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
        //
    }

    public function mount(Game $game)
    {
        $this->game = $game;

        if ($this->game->state()->status === 'in-progress') {
            return redirect()->route('games.auction', [
                'game' => $this->game,
                'round' => $this->game->currentRound(),
            ]);
        }

        $this->initializeProperties();

    }

    public $game_status;

    public function initializeProperties()
    {
        $this->game_status = $this->game->state()->status;
    }

    public function startGame()
    {
        try {
            $this->game->start();
        } catch (\Throwable $th) {
            //
        }

        return redirect()->route('games.auction', [
            'game' => $this->game,
            'round' => $this->game->currentRound(),
        ]);
    }

    public function render()
    {
        return view('livewire.pre-game-lobby');
    }
}
