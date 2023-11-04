<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\GameCreated;
use Illuminate\Support\Facades\Auth;

class InitializeGame extends Component
{
    public function createGame()
    {
        GameCreated::fire(
            user_id: Auth::user()->id
        );
    }

    public function render()
    {
        return view('livewire.initialize-game');
    }
}
