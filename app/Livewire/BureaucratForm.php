<?php

namespace App\Livewire;

use App\Models\Player;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class BureaucratForm extends Component
{
    #[Modelable]
    public int $offer = 0;
    
    #[Reactive]
    public int $money;

    public ?array $data;

    public $bureaucrat;

    public Player $player;

    public function mount(string $bureaucrat, int $money, Player $player)
    {
        $this->bureaucrat = $bureaucrat;
        $this->money = $money;
        $this->player = $player;

        $data_array = $bureaucrat::expectedData($this->player->game->currentRound(), $this->player);

        $this->data = $data_array ?? null;
    }

    public function increment()
    {
        if ($this->offer > $this->money) {
            return;
        }
          
        $this->offer++;
    }

    public function decrement()
    {
        if ($this->offer < 0) {
            return;
        }
          
        $this->offer--;
    }

    public function render()
    {
        return view('livewire.bureaucrat-form');
    }
}
