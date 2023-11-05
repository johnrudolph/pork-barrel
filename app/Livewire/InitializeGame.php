<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;
use Glhd\Bits\Snowflake;
use App\Events\GameCreated;
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
        $event = GameCreated::fire(
            game_id: Snowflake::make()->id(),
            user_id: $this->user()->id
        );

        PlayerJoinedGame::fire(
            game_id: $event->game_id,
            user_id: $this->user()->id,
        );

        return redirect()->route('games.show', ['game' => $event->game_id]);
    }

    public function joinGame()
    {
        try {
            $game = Game::firstWhere('code', $this->game_code);

            PlayerJoinedGame::fire(
                game_id: $game->id,
                user_id: $this->user()->id,
            );
        } catch (\Exception $e) {
            
        }
        


        return redirect()->route('games.show', ['game' => $game->id]);
    }

    public function render()
    {
        return view('livewire.initialize-game');
    }
}
