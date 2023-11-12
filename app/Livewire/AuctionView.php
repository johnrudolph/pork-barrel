<?php

namespace App\Livewire;

use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class AuctionView extends Component
{
    public $player_id;

    public $game;

    public $bids;

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

        $this->bids = $round->state()->bureaucrats->map(function ($b) {
            return [
                'slug' => $b::SLUG,
                'class' => $b,
                'bid' => 0,
            ];
        });
    }





    public function increment($bureacrat_slug)
    {
        $this->bids->firstWhere('slug', $bureacrat_slug)['bid'] += 100;
        dd($this->bids->firstWhere('slug', $bureacrat_slug)['bid']);
    }

    public function decrement($bureacrat_slug)
    {
        $this->bids->firstWhere('slug', $bureacrat_slug)['bid']--;
    }
    
    public function render()
    {
        return view('livewire.auction-view');
    }
}
