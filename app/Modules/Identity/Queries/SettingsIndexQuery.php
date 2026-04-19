<?php

namespace App\Modules\Identity\Queries;

use App\Modules\Identity\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SettingsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $group = $request->string('group')->toString();

        return Setting::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('label', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%");
                });
            })
            ->when($group !== '', fn (Builder $query) => $query->where('group', $group))
            ->orderBy('group')
            ->orderBy('label');
    }
}
