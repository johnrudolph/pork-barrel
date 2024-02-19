<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\DTOs\OfferDTO;
use Livewire\Component;
use App\States\RoundState;
use Livewire\Attributes\On;
use App\Events\AuctionEnded;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Events\PlayerAwaitingResults;

class AuctionView extends Component
{
    public Game $game;

    public Round $round;

    public $offers;

    public int $money;

    protected $listeners = [
        'echo:games.{game.id},GameUpdated' => '$refresh',
        'echo:players.{player.id},PlayerUpdated' => '$refresh',
    ];

    #[On('echo:games.{game.id},GameUpdated')]
    public function gameUpdated()
    {
        //
    }

    #[On('echo:players.{player.id},PlayerUpdated')]
    public function playerUpdated()
    {
        //
    }

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    #[Computed]
    public function player()
    {
        return $this->game->players->firstWhere('user_id', $this->user->id);
    }

    public $game_status;
    public $round_status;
    public $player_status;
    public $round_template;

    public function mount(Game $game, Round $round)
    {
        if($game->currentRound()->id !== $round->id) {
            return redirect()->route('games.auction', [
                'game' => $game, 
                'round' => $game->currentRound(),
            ]);
        }

        if ($round->state()->status !== 'auction') {
            return redirect()->route('games.waiting', [
                'game' => $game, 
                'round' => $game->currentRound(),
            ]);
        }

        // @todo if player has already submitted this round, redirect to correct page.
        // also should probably validate on submit offer to make sure they can
        $this->game = $game;
        $this->round = $round;
        $this->initializeProperties($game, $round);
    }

    public function initializeProperties(Game $game, Round $round)
    {
        $this->money = $this->player->state()->availableMoney();

        foreach ($this->round->state()->bureaucrats as $b) {
            $this->offers[$b::SLUG] = new OfferDTO(
                player_id: $this->player->id,
                round_id: $this->round->id,
                bureaucrat: $b,
            );
        }

        $this->round_template = $this->round->state()->round_template;
        $this->game_status = $this->game->state()->status;
        $this->round_status = $this->round->state()->status;
        $this->player_status = $this->player->state()->status;
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

        try {
            collect($this->offers)
                ->filter(fn ($o) => $o->amount_offered > 0)
                ->each(fn ($o) => $o->submit());
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        PlayerAwaitingResults::fire(
            player_id: $this->player->id,
            round_id: $this->round->id,
        );

        if (
            $this->game->state()->playerStates()
                ->filter(fn ($p) => $p->status === 'waiting'
                    && $p->current_round_id === $this->round->id
                )
                ->count() === $this->game->players->count()
        ) {
            AuctionEnded::fire(round_id: $this->round->id);
        }

        return redirect()->route('games.waiting', [
            'game' => $this->game, 
            'round' => $this->round,
        ]);
    }

    public function render()
    {
        return view('livewire.auction-view');
    }
}
