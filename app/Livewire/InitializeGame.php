<?php

namespace App\Livewire;

use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use App\Models\User;
use Glhd\Bits\Snowflake;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Thunk\Verbs\Facades\Verbs;

class InitializeGame extends Component
{
    public string $game_code;

    #[Computed]
    public function user(): User
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
            'game' => Game::find($game_id),
        ]);
    }

    public function createTransparentGame()
    {
        $game_id = GameCreated::fire(
            user_id: $this->user()->id,
            is_transparent: true
        )->game_id;

        Verbs::commit();

        return redirect()->route('games.pre-game', [
            'game' => Game::find($game_id),
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
