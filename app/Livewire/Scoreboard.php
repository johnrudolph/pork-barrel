<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\States\RoundState;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Scoreboard extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public $scores;

    public $offers_made;

    #[Computed]
    public function player()
    {
        return Auth::user()->currentPlayer();
    }

    #[Computed]
    public function round()
    {
        return RoundState::load($this->player()->state()->current_round_id);
    }

    public function mount(Player $player)
    {
        $this->initializeProperties();
    }

    public function initializeProperties()
    {
        $this->scores = $this->game->state()->playerStates()
            ->map(fn ($p) => [
                'player_id' => $p->id,
                'industry' => $p->industry,
                'money' => $p->money,
            ]);
    }

    public function render()
    {
        return view('livewire.scoreboard');
    }
}
