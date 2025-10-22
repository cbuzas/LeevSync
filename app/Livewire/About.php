<?php

namespace App\Livewire;

use Livewire\Component;
use Native\Laravel\Facades\Shell;

class About extends Component
{

    public function openExternal($url)
    {
        Shell::openExternal($url);
    }

    public function render()
    {
        return view('livewire.about');
    }
}
