<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use App\Events\MyEvent;
use Livewire\Component;
use App\States\RoundState;
use Livewire\Attributes\On;
use App\Events\PlayerReadiedUp;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class AwaitingNextRoundView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public Round $round;

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
        $this->round = $this->game->currentRound();
    
        $this->offers_made = $this->round()->offers
            ->filter(fn ($o) => $o->player_id === $this->player()->id)
            ->map(fn ($o) => [
                'bureaucrat' => $o->bureaucrat,
                'offer' => $o->modified_amount,
                'awarded' => $o->awarded,
                'is_blocked' => $o->is_blocked,
            ]);
    }

    public function readyUp()
    {
        PlayerReadiedUp::fire(player_id: $this->player()->id, game_id: $this->game->id);

        $this->dispatch('readied-up'); 
    }

    #[On('echo:games.{game.id},GameUpdated')]
    public function roundEnded()
    {
        $this->dispatch('round-ended');
    }

    public function render()
    {
        return view('livewire.awaiting-next-round-view');
    }
}
