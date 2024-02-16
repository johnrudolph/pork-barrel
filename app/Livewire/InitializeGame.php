<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;
use Glhd\Bits\Snowflake;
use App\Events\GameCreated;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerJoinedGame;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class InitializeGame extends Component
{
    public string $game_code;

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    public function createGame()
    {
        $game_id = GameCreated::fire(
            user_id: $this->user()->id
        )->game_id;

        Verbs::commit();

        return redirect()->route('games.pre-game', [
            'game' => Game::find($game_id)
        ]);
    }

    public function joinGame()
    {
        $game = Game::firstWhere('code', $this->game_code);

        PlayerJoinedGame::fire(
            game_id: $game->id,
            player_id: Snowflake::make()->id(),
            user_id: $this->user()->id,
            name: $this->user()->name,
        );

        return redirect()->route('games.pre-game', ['game' => $game->id]);
    }

    public function render()
    {
        return view('livewire.initialize-game');
    }
}
