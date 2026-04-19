<?php

namespace App\Modules\Students\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Queries\AdminMistakesIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MistakeController extends Controller
{
    public function __construct(private readonly AdminMistakesIndexQuery $adminMistakesIndexQuery)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MistakeItem::class);

        return view('admin.students.mistakes.index', [
            'items' => $this->adminMistakesIndexQuery->builder($request)->paginate(20)->withQueryString(),
        ]);
    }

    public function show(MistakeItem $mistakeItem): View
    {
        $this->authorize('view', $mistakeItem);

        return view('admin.students.mistakes.show', [
            'item' => $mistakeItem->load(['student', 'lecture', 'exam']),
        ]);
    }
}
