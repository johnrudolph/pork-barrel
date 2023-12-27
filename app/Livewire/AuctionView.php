<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\DTOs\OfferDTO;
use App\Models\Player;
use Livewire\Component;
use App\Events\AuctionEnded;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Events\PlayerAwaitingResults;

class AuctionView extends Component
{
    public Game $game;

    public $offers;

    public int $money;

    public Player $player;

    protected $listeners = [
        'echo:games.{game.id},GameUpdated' => '$refresh',
        'echo:players.{player.id},PlayerUpdated' => '$refresh',
    ];

    #[Computed]
    public function round()
    {
        return $this->game->currentRound();
    }

    public function mount(Player $player)
    {
        $this->initializeProperties($player, $this->game->currentRound());
    }

    public function initializeProperties(Player $player, Round $round)
    {
        $this->player = Auth::user()->currentPlayer();

        $this->money = $this->player->state()->money;

        foreach ($this->round->state()->bureaucrats as $b) {
            $this->offers [$b::SLUG] = new OfferDTO(
                player_id: $this->player->id,
                round_id: $this->round->id,
                bureaucrat: $b,
            );
        }
    }

    public function increment($bureacrat_slug)
    {
        if (collect($this->offers)->sum('offer') < $this->money) {
            $this->offers[$bureacrat_slug]->amount_offered++;
            $this->money--;
        } else {
            // @todo: tell the user they don't have enough money
        }
    }

    public function decrement($bureacrat_slug)
    {
        if ($this->offers[$bureacrat_slug]->amount_offered > 0) {
            $this->offers[$bureacrat_slug]->amount_offered--;
            $this->money++;
        }
    }

    public function submit()
    {
        collect($this->offers)
            ->filter(fn ($o) => $o->amount_offered > 0)
            ->each(function ($o) {
                if (collect($o->data)->contains(null)) {
                    dd('Please fill out options for '.$o->bureaucrat::NAME.'.');
                }
            })
            ->each(fn ($o) => $o->submit());

        // @todo this doesn't work. 
        // collect($this->offers)->each(function ($o) {
        //     if ($o->amount_offered > 0) {
        //         $this->validate($o->rules);
        //     }
        // })->each(fn ($o) => $o->submit());

        PlayerAwaitingResults::fire(player_id: $this->player->id);

        if (
            $this->game->state()->playerStates()
                ->filter(fn ($p) => $p->status === 'waiting')
                ->count() === $this->game->players->count()
        ) {
            AuctionEnded::fire(round_id: $this->round->id);
        }
    }

    public function render()
    {
        return view('livewire.auction-view');
    }
}
