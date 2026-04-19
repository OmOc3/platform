<?php

namespace App\Shared\Livewire;

use Livewire\Component;

class SupportWidget extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function render()
    {
        return view('livewire.shared.support-widget', [
            'support' => config('platform.public.support'),
            'brand' => config('platform.brand'),
            'student' => auth('student')->user(),
        ]);
    }
}
