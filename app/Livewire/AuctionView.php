<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Round;
use App\Models\Player;
use Livewire\Component;
use App\Events\AuctionEnded;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Events\PlayerAwaitingResults;

class AuctionView extends Component
{
    public Game $game;

    public array $bureaucrats;

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

        $this->bureaucrats = collect($round->state()->bureaucrats)->mapWithKeys(function ($b) {
            $data_array = $b::expectedData($this->game->currentRound(), $this->player);

            return [$b::SLUG => ['class' => $b, 'offer' => 0, 'data' => $data_array ?? null]];
        })->toArray();
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
            ->filter(fn ($b) => $b['offer'] > 0)
            ->each(fn ($b) => $this->player
                ->submitOffer($this->round, $b['class'], $b['offer'], $b['data'] ?? null)
            );

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
