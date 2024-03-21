<?php

namespace App\Livewire;

use App\Events\PlayerReadiedUp;
use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AwaitingNextRoundView extends Component
{
    public ?int $player_id = null;

    public Game $game;

    public Player $player;

    public Round $round;

    public $offers = [];

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

    public function mount(Game $game, Round $round)
    {
        $this->player = Auth::user()->currentPlayer();
        $this->game = $game;
        $this->round = $round;

        $this->initializeProperties();
    }

    public function initializeProperties()
    {
        $this->offers = $this->round->state()->offers()
            ->map(function ($o) {
                $modification_description = collect($o->amount_modifications)->count() > 0
                    ? 'Original offer: '.$o->amount_offered.' ('.collect($o->amount_modifications)->map(fn ($m) => $m['description'])->join(', ').')'
                    : null;

                return [
                    'bureaucrat' => $o->bureaucrat,
                    'industry' => $o->player()->industry,
                    'player_id' => $o->player_id,
                    'offer' => $o->netOffer(),
                    'awarded' => $o->awarded,
                    'is_blocked' => $o->is_blocked,
                    'modifications' => $modification_description,
                ];
            })->sortByDesc('offer');
    }

    public function readyUp()
    {
        PlayerReadiedUp::fire(
            player_id: $this->player->id,
            game_id: $this->game->id,
            round_id: $this->round->id
        );

        return redirect()->route('games.auction', [
            'game' => $this->game,
            'round' => $this->round->next(),
        ]);
    }

    public function seeFinalScores()
    {
        return redirect()->route('games.final_scores', [
            'game' => $this->game,
        ]);
    }

    public function render()
    {
        return view('livewire.awaiting-next-round-view');
    }
}
