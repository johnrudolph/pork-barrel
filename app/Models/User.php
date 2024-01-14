<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasSnowflakes, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_game_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function currentGame()
    {
        return $this->belongsTo(Game::class, 'current_game_id');
    }

    public function currentPlayer()
    {
        return $this->currentGame->players->where('user_id', $this->id)->first();
    }
}
