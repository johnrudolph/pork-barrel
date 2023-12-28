<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\MoneyLogEntry;
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
    public function otherHeadlines()
    {
        return $this->game->headlines;
    }

    #[Computed]
    public function scores()
    {
        return $this->game->state()->playerStates()
            ->map(fn ($p) => [
                'player_id' => $p->id,
                'industry' => $p->industry,
                'money' => $p->money,
            ]);
    }

    #[Computed]
    public function moneyLogEntries()
    {
        return MoneyLogEntry::where('player_id', $this->player->id)
            ->get()
            ->sortByDesc('created_at');
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
