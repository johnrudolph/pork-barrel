<?php

namespace App\Livewire;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class PreGameView extends Component
{
    public $game;

    #[On('echo:games.{game.id},GameUpdated')]
    public function gameUpdated()
    {
        //
    }

    #[On('echo:players.{player.id},PlayerUpdated')]
    public function playerUpdated()
    {
        //
    }

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    #[Computed]
    public function player()
    {
        return $this->user->currentPlayer();
    }

    public function mount(Game $game)
    {
        $this->game = $game;

        if ($this->game->state()->status === 'in-progress') {
            return redirect()->route('games.auction', [
                'game' => $this->game, 
                'round' => $this->game->currentRound()
            ]);
        };

        $this->initializeProperties();

    }

    public $game_status;

    public function initializeProperties()
    {
        $this->game_status = $this->game->state()->status;
    }

    public function render()
    {
        return view('livewire.pre-game-view', [
            'game' => $this->game,
        ]);
    }
}
