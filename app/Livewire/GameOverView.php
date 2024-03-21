<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GameOverView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public Player $player;

    public Round $round;

    public $offers = [];

    protected $listeners = [
        'echo:games.{game.id},GameUpdated' => '$refresh',
        'echo:players.{player.id},PlayerUpdated' => '$refresh',
    ];

    public function mount(Game $game)
    {
        $this->player = Auth::user()->currentPlayer();
        $this->game = $game;
        $this->round = $game->rounds->last();
    }

    public function scores()
    {
        return $this->game->players->map(fn ($p) => [
            'industry' => $p->state()->industry,
            'player_id' => $p->id,
            'player_name' => $p->user->name,
            'money' => $p->state()->availableMoney(),
        ])->sortByDesc('money');
    }

    public function render()
    {
        return view('livewire.game-over-view');
    }
}
