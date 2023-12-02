<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use App\Models\MoneyLogEntry;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MoneyLog extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public array $bureaucrats;

    public int $money;

    public array $offers;

    public Collection $entries;

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

        $this->entries = MoneyLogEntry::where('player_id', $this->player()->id)
            ->get()
            ->sortByDesc('created_at');
    }

    public function render()
    {
        return view('livewire.money-log');
    }
}
