<?php

namespace App\Shared\Support\Navigation;

use App\Modules\Identity\Models\Admin;

class AdminNavigation
{
    /**
     * @return array<int, array{label: string, items: array<int, array<string, string>>}>
     */
    public function sections(?Admin $admin): array
    {
        return collect(config('platform.admin_menu', []))
            ->map(function (array $section) use ($admin): ?array {
                $items = collect($section['items'] ?? [])
                    ->filter(function (array $item) use ($admin): bool {
                        $permission = $item['permission'] ?? null;

                        if ($permission === null) {
                            return true;
                        }

                        return $admin?->can($permission) ?? false;
                    })
                    ->values()
                    ->all();

                if ($items === []) {
                    return null;
                }

                return [
                    'label' => $section['label'],
                    'items' => $items,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
