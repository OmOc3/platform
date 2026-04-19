<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Students\Actions\Profiles\UpdateStudentProfileAction;
use App\Modules\Students\Http\Requests\Student\UpdateOwnProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly UpdateStudentProfileAction $updateStudentProfileAction)
    {
    }

    public function show(): View
    {
        $student = auth('student')->user();

        $this->authorize('viewProfile', $student);

        return view('student.profile.show', [
            'student' => $student->load(['grade', 'track', 'group']),
        ]);
    }

    public function update(UpdateOwnProfileRequest $request): RedirectResponse
    {
        $student = auth('student')->user();

        $this->authorize('updateProfile', $student);

        $this->updateStudentProfileAction->execute($student, $request->validated());

        return redirect()
            ->route('student.profile.show')
            ->with('status', 'تم تحديث بياناتك بنجاح.');
    }
}
