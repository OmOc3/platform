<?php

namespace App\Shared\Support\Navigation;

use Illuminate\Support\Str;

class StudentNavigation
{
    /**
     * @return array<int, array{label: string, href: string, active: bool}>
     */
    public function items(): array
    {
        return collect(config('platform.student_menu', []))
            ->map(function (array $item): array {
                $route = $item['route'];
                $segments = explode('.', $route);
                $active = request()->routeIs($route);

                if (count($segments) > 2) {
                    $active = $active || request()->routeIs(Str::beforeLast($route, '.').'.*');
                }

                return [
                    'label' => $item['label'],
                    'href' => route($route),
                    'active' => $active,
                ];
            })
            ->all();
    }
}
