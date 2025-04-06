<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TextArea extends Component
{
    public function __construct(
        public bool $disabled = false
    ) {}

    public function render()
    {
        return view('components.text-area');
    }
}
