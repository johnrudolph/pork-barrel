<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class DecisionView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public array $actions;

    public int $money;

    public array $decisions = [];

    #[Computed]
    public function player()
    {
        return Auth::user()->currentPlayer();
    }

    public function mount(Player $player)
    {
        $this->initializeProperties($player, $this->game->currentRound());
    }

    public function initializeProperties(Player $player, Round $round)
    {
        $this->player_id = $player->id;

        $this->money = $this->player()->state()->money;

        $this->actions = collect($round->state()->actions[$this->player()->id])
            ->pluck('class')
            ->toArray();

        $this->decisions = collect($round->state()->actions[$this->player()->id])
            ->filter(fn ($a) => $a['class']::EFFECT_REQUIRES_DECISION)
            ->mapWithKeys(fn ($a) => [$a['class'] => null])
            ->toArray();
    }

    public function submit()
    {
        // @todo: give them some kind confirmation and remove the UI for bids so it's not confusing
        // @todo: submit actions
    }
    public function render()
    {
        return view('livewire.decision-view');
    }
}
