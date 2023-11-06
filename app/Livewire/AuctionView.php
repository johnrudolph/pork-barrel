<?php

namespace App\Livewire;

use Livewire\Component;

class AuctionView extends Component
{
    public $game;
    
    public function render()
    {
        return view('livewire.auction-view');
    }
}
