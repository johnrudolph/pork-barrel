<?php

namespace App\Livewire;

use App\Bureaucrats\Bureaucrat;
use App\RoundModifiers\RoundModifier;
use Livewire\Component;

class RulesPage extends Component
{
    public $bureaucrats;

    public $round_modifiers;

    public function mount()
    {
        $this->bureaucrats = Bureaucrat::all();

        $this->round_modifiers = RoundModifier::all();
    }

    public function render()
    {
        return view('livewire.rules-page');
    }
}
