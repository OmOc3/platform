<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PackagesIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $featured = $request->string('featured')->toString();

        return Package::query()
            ->with(['product', 'items'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('product', function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($featured !== '', fn (Builder $query) => $query->where('is_featured', $featured === '1'))
            ->orderByDesc('is_featured')
            ->orderBy('lecture_count');
    }
}
