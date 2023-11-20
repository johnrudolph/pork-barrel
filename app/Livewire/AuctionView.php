<?php

namespace App\Livewire;

use App\DTOs\Offer;
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

    public array $bureaucrats;

    public int $money;

    public array $offers;

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

        $this->bureaucrats = collect($round->state()->bureaucrats)->mapWithKeys(function ($b) {
            return [$b::SLUG => ['class' => $b, 'offer' => 0]];
        })->toArray();

        $this->offers = collect($round->state()->offers)
            ->filter(fn ($o) => $o['player_id'] === $this->player()->id)
            ->mapWithKeys(fn ($o) => [$o['bureaucrat'] => $o['amount']])
            ->toArray();
    }

    public function increment($bureacrat_slug)
    {
        if (collect($this->bureaucrats)->sum('offer') < $this->money) {
            $this->bureaucrats[$bureacrat_slug]['offer']++;
        }
    }

    public function decrement($bureacrat_slug)
    {
        if ($this->bureaucrats[$bureacrat_slug]['offer'] > 0) {
            $this->bureaucrats[$bureacrat_slug]['offer']--;
        }
    }

    public function submit()
    {
        collect($this->bureaucrats)
            ->filter(fn ($b) => $b['offer'] > 0)
            ->each(fn ($b) => 
                $this->player
                    ->submitOffer($this->game->currentRound(), $b['class'],$b['offer'],)
            );

        if (
            collect($this->game->currentRound()->state()->offers)
                ->pluck('player_id')
                ->unique()
                ->count() === $this->game->players->count()
        ) {
            $this->game->currentRound()->endAuctionPhase();
        }
        
        $this->initializeProperties($this->player(), $this->game->currentRound());
    }

    public function render()
    {
        return view('livewire.auction-view');
    }
}
