<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

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
        // dd($this->game->state());
        // dd($this->game->currentRound());
    }

    public function render()
    {
        return view('livewire.game-view', [
            'game' => $this->game,
        ]);
    }
}
