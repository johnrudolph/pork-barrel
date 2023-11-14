<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class AuctionView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public array $bids;

    public int $money;

    #[Computed]
    public function bureacrats()
    {
        return $this->game->currentRound()->state()->bureaucrats;
    }

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

        dd($round->state());
        $this->bids = $round->state()->bureaucrats->mapWithKeys(function ($b) {
            return [$b::SLUG => ['class' => $b, 'bid' => 0]];
        })->toArray();
    }

    public function increment($bureacrat_slug)
    {
        if (collect($this->bids)->sum('bid') < $this->money) {
            $this->bids[$bureacrat_slug]['bid']++;
        }
    }

    public function decrement($bureacrat_slug)
    {
        if  ($this->bids[$bureacrat_slug]['bid'] > 0) {
            $this->bids[$bureacrat_slug]['bid']--;
        }
    }

    public function submit()
    {
        dd($this->player->state(), $this->bids);

        BidSubmitted::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            bids: $this->bids
        );
    }
    
    public function render()
    {
        return view('livewire.auction-view');
    }
}
