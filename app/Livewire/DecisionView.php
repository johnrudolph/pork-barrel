<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DecisionView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public $actions;

    public int $money;

    public array $decisions;

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

        $this->actions = $round->state()->actionsWonBy($this->player()->id);

        $this->decisions = $this->actions
            ->filter(fn ($a) => $a::EFFECT_REQUIRES_DECISION)
            ->mapWithKeys(fn ($a) => [$a => null])
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
