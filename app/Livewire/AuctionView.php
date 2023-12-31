<?php

namespace App\Livewire;

use App\DTOs\OfferDTO;
use App\Events\AuctionEnded;
use App\Events\PlayerAwaitingResults;
use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

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
            $this->offers[$b::SLUG] = new OfferDTO(
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

    public function offerDataIsValid()
    {
        $errors = collect($this->offers)
            ->filter(fn ($o) => $o->amount_offered > 0 && $o->rules)
            ->map(function ($o) {
                return $o->validate()->errors()->all()
                    ? 'Please fill out options for '.$o->bureaucrat::NAME.'.'
                    : null;
            })
            ->filter(fn ($e) => $e !== null);

        if ($errors->count() === 0) {
            session()->forget('error');

            return true;
        }

        session()->flash('error', $errors->implode(' '));

        return false;
    }

    public function submit()
    {
        if (! $this->offerDataIsValid()) {
            return;
        }

        collect($this->offers)
            ->filter(fn ($o) => $o->amount_offered > 0)
            ->each(fn ($o) => $o->submit());

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
