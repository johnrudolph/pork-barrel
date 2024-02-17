<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Events\PlayerReadiedUp;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class AwaitingNextRoundView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public Player $player;

    public Round $round;

    protected $listeners = [
        'echo:games.{game.id},GameUpdated' => '$refresh',
        'echo:players.{player.id},PlayerUpdated' => '$refresh',
    ];

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
    public function offersMade()
    {
        return $this->round->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->player->id)
            ->map(fn ($o) => [
                'bureaucrat' => $o->bureaucrat,
                'offer' => $o->amount_offered + $o->amount_modified,
                'awarded' => $o->awarded,
                'is_blocked' => $o->is_blocked,
            ]);
    }

    public function mount(Game $game, Round $round)
    {
        $this->player = Auth::user()->currentPlayer();
        $this->game = $game;
        $this->round = $round;
    }

    public function readyUp()
    {
        PlayerReadiedUp::fire(
            player_id: $this->player->id, 
            game_id: $this->game->id,
            round_id: $this->round->id
        );

        return redirect()->route('games.auction', [
            'game' => $this->game,
            'round' => $this->round->next(),
        ]);
    }

    public function render()
    {
        return view('livewire.awaiting-next-round-view');
    }
}
