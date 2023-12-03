<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Thunk\Verbs\Facades\Verbs;

class AuctionView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public array $bureaucrats;

    public int $initial_money;

    public array $offers;

    public $foo = 7;

    #[Computed]
    public function money()
    {
        return $this->initial_money - collect($this->bureaucrats)->sum('offer');
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

        $this->initial_money = $this->player()->state()->money;

        $this->bureaucrats = collect($round->state()->bureaucrats)->mapWithKeys(function ($b) {
            $data_array = $b::expectedData($this->game->currentRound(), $this->player());

            return [$b => ['class' => $b, 'offer' => 0, 'data' => $data_array ?? null]];
        })->toArray();

        $this->offers = collect($round->state()->offers)
            ->filter(fn ($o) => $o['player_id'] === $this->player()->id)
            ->mapWithKeys(fn ($o) => [$o['bureaucrat'] => $o['amount']])
            ->toArray();
    }

    public function submit()
    {
        dd($this->bureaucrats);
        collect($this->bureaucrats)
            ->filter(fn ($b) => $b['offer'] > 0)
            ->each(fn ($b) => $this->player
                ->submitOffer($this->game->currentRound(), $b['class'], $b['offer'], $b['data'] ?? null)
            );

        if (
            collect($this->game->currentRound()->state()->offers)
                ->pluck('player_id')
                ->unique()
                ->count() === $this->game->players->count()
        ) {
            $this->game->currentRound()->endAuctionPhase();
            Verbs::commit();
            $this->game->currentRound()->endRound();
            Verbs::commit();
            $this->game->currentRound()->next()->start();
        }

        $this->initializeProperties($this->player(), $this->game->currentRound());
    }

    public function render()
    {
        return view('livewire.auction-view');
    }
}
