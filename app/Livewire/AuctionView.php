<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use App\Events\RoundEnded;
use Thunk\Verbs\Facades\Verbs;
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
            $data_array = $b::expectedData($this->game->currentRound(), $this->player());

            return [$b::SLUG => ['class' => $b, 'offer' => 0, 'data' => $data_array ?? null]];
        })->toArray();

        $this->offers = collect($round->state()->offers)
            ->filter(fn ($o) => $o->player_id === $this->player()->id)
            ->mapWithKeys(fn ($o) => [$o->bureaucrat => $o->modified_amount])
            ->toArray();
    }

    public function increment($bureacrat_slug)
    {
        if (collect($this->bureaucrats)->sum('offer') < $this->money) {
            $this->bureaucrats[$bureacrat_slug]['offer']++;
        } else {
            // @todo: tell the user they don't have enough money
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
            RoundEnded::fire(round_id: $this->game->currentRound()->id);
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
