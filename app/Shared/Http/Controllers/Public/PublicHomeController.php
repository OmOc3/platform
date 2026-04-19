<?php

namespace App\Shared\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Queries\FeaturedBooksQuery;
use App\Modules\Commerce\Queries\FeaturedPackagesQuery;
use Illuminate\Contracts\View\View;

class PublicHomeController extends Controller
{
    public function __construct(
        private readonly FeaturedPackagesQuery $featuredPackagesQuery,
        private readonly FeaturedBooksQuery $featuredBooksQuery,
    ) {
    }

    public function __invoke(): View
    {
        return view('public.home', [
            'featuredPackages' => $this->featuredPackagesQuery->get(),
            'featuredBooks' => $this->featuredBooksQuery->get(),
            'publicContent' => config('platform.public', []),
        ]);
    }
}
