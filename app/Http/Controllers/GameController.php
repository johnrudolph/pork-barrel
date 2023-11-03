<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\PlayerJoinedGame;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        $game = Game::create([
            'code' => Str::random(4),
        ]);

        PlayerJoinedGame::fire(
            game_id: $game->id,
            user_id: Auth::user()->id,
        );

        return redirect()->route('games.show', $game);
    }

    public function show(string $id)
    {
        return view('game', [
            'game' => Game::findOrFail($id),
        ]);
    }
}
