<?php

namespace App\Shared\Support\Navigation;

class StudentNavigation
{
    /**
     * @return array<int, array{label: string, href: string}>
     */
    public function items(): array
    {
        return config('platform.student_menu', []);
    }
}
