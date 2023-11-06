<?php

namespace App\Livewire;

use Livewire\Component;

class AuctionView extends Component
{
    public $game;

    public $bids;

    public function mount()
    {
        $this->bids = $this->game->currentRound()->state()->bureaucrats
            ->map(fn ($b) => [
                'class' => $b,
                'slug' => $b::SLUG,
                'bid' => 0
            ]);
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
