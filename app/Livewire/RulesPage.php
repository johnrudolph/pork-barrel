<?php

namespace App\Livewire;

use App\Bureaucrats\Bureaucrat;
use App\RoundTemplates\RoundTemplate;
use Livewire\Component;

class RulesPage extends Component
{
    public $bureaucrats;

    public $round_templates;

    public function mount()
    {
        $this->bureaucrats = Bureaucrat::all();

        $this->round_templates = RoundTemplate::all();
    }

    public function render()
    {
        return view('livewire.rules-page');
    }
}
