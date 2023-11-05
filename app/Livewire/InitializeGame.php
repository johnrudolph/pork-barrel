<?php

namespace App\Livewire;

use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use Glhd\Bits\Snowflake;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

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
        $event = GameCreated::fire(
            game_id: Snowflake::make()->id(),
            user_id: $this->user()->id
        );

        PlayerJoinedGame::fire(
            game_id: $event->game_id,
            player_id: Snowflake::make()->id(),
            user_id: $this->user()->id,
        );

        return redirect()->route('games.show', ['game' => $event->game_id]);
    }

    public function joinGame()
    {
        $game = Game::firstWhere('code', $this->game_code);

        PlayerJoinedGame::fire(
            game_id: $game->id,
            player_id: Snowflake::make()->id(),
            user_id: $this->user()->id,
        );

        return redirect()->route('games.show', ['game' => $game->id]);
    }

    public function render()
    {
        return view('livewire.initialize-game');
    }
}
