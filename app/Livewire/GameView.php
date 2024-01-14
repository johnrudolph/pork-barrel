<?php

namespace App\Livewire;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class GameView extends Component
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

    #[Computed]
    public function round()
    {
        return $this->game->currentRound();
    }

    #[Computed]
    public function roundModifier()
    {
        return $this->game->currentRound()->state()->round_modifier;
    }

    #[Computed]
    public function headlines()
    {
        return $this->game->headlines->sortByDesc('created_at');
    }

    #[Computed]
    public function scores()
    {
        return $this->game->state()->playerStates()
            ->map(fn ($p) => [
                'player_id' => $p->id,
                'industry' => $p->industry,
                'money' => $p->availableMoney(),
            ]);
    }

    #[Computed]
    public function moneyLogEntries()
    {
        return $this->player->state()->money_history
            ->reverse();
    }

    public function mount($game)
    {
        $this->game = Game::find($game);
    }

    public function render()
    {
        return view('livewire.game-view', [
            'game' => $this->game,
        ]);
    }
}
