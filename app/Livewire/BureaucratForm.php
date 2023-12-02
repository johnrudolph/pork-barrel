<?php

namespace App\Livewire;

use App\Models\Player;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class BureaucratForm extends Component
{
    #[Modelable]
    public array $offer = [
        'bureaucrat' => null,
        'player' => null,
        'amount' => 0,
        'data' => null
    ];

    #[Reactive]
    public int $money;

    public $bureaucrat;

    public Player $player;

    public function mount($bureaucrat, $money, $player)
    {
        $this->bureaucrat = $bureaucrat;
        $this->money = $money;
        $this->player = $player;

        $data_array = $bureaucrat::expectedData($this->player->game->currentRound(), $this->player);

        $this->offer = [
            'bureaucrat' => $bureaucrat,
            'player' => $player,
            'amount' => 0,
            'data' => $data_array ?? null
        ];
    }

    public function increment()
    {
        if ($this->offer['amount'] > $this->money) {
            return;
        }
          
        $this->offer['amount']++;
    }

    public function decrement()
    {
        if ($this->offer['amount'] < 0) {
            return;
        }
          
        $this->offer['amount']--;
    }

    public function render()
    {
        return view('livewire.bureaucrat-form');
    }
}
