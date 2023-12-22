<?php

namespace App\Livewire;

use App\Events\PlayerReadiedUp;
use App\Models\Game;
use App\Models\Player;
use App\States\RoundState;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AwaitingNextRoundView extends Component
{
    public ?int $player_id = null;

    public Game $game;

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
    }

    public function render()
    {
        return view('livewire.awaiting-next-round-view');
    }
}
