<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\Tracks\CreateTrackAction;
use App\Modules\Academic\Actions\Tracks\UpdateTrackAction;
use App\Modules\Academic\Http\Requests\Admin\Tracks\StoreTrackRequest;
use App\Modules\Academic\Http\Requests\Admin\Tracks\UpdateTrackRequest;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Queries\TracksIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackController extends Controller
{
    public function __construct(
        private readonly TracksIndexQuery $tracksIndexQuery,
        private readonly CreateTrackAction $createTrackAction,
        private readonly UpdateTrackAction $updateTrackAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Track::class);

        $query = $this->tracksIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('tracks.csv', ['المسار', 'الصف', 'الكود', 'الحالة'], $query->get()
                ->map(fn (Track $track): array => [
                    $track->name_ar,
                    $track->grade?->name_ar,
                    $track->code,
                    $track->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.tracks.index', [
            'tracks' => $query->paginate(15)->withQueryString(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Track::class);

        return view('admin.academic.tracks.create', [
            'track' => new Track(['is_active' => true]),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreTrackRequest $request): RedirectResponse
    {
        $this->authorize('create', Track::class);

        $this->createTrackAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.tracks.index')
            ->with('status', 'تم إنشاء المسار.');
    }

    public function edit(Track $track): View
    {
        $this->authorize('update', $track);

        return view('admin.academic.tracks.edit', [
            'track' => $track,
            'grades' => Grade::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateTrackRequest $request, Track $track): RedirectResponse
    {
        $this->authorize('update', $track);

        $this->updateTrackAction->execute($track, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.tracks.index')
            ->with('status', 'تم تحديث المسار.');
    }

    public function destroy(Track $track): RedirectResponse
    {
        $this->authorize('delete', $track);

        $oldValues = $track->toArray();
        $track->delete();

        $this->auditLogger->log(
            event: 'academic.track.deleted',
            actor: auth('admin')->user(),
            subject: $track,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.tracks.index')
            ->with('status', 'تم حذف المسار.');
    }
}
