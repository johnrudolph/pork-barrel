<?php

namespace App\Livewire;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GameView extends Component
{
    public $game;

    public $user;

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    public function mount($game)
    {
        $this->game = Game::find($game);
    }

    public function render()
    {
        return view('livewire.game-view');
    }
}
