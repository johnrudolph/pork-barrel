<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class GameView extends Component
{
    public $game;
    public $user;

    public function mount($game)
    {
        // $this->game = Game::findBySnowflake($game);
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('livewire.game-view');
    }
}
