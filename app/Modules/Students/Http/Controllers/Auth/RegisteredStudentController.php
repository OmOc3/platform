<?php

namespace App\Modules\Students\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Students\Actions\Auth\RegisterStudentAction;
use App\Modules\Students\Actions\Auth\UpdateStudentLastLoginAction;
use App\Modules\Students\Http\Requests\Auth\StudentRegistrationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredStudentController extends Controller
{
    public function __construct(
        private readonly RegisterStudentAction $registerStudentAction,
        private readonly UpdateStudentLastLoginAction $updateStudentLastLoginAction,
    ) {
    }

    public function create(): View
    {
        return view('student.auth.register', [
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StudentRegistrationRequest $request): RedirectResponse
    {
        $student = $this->registerStudentAction->execute($request->validated());

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        $this->updateStudentLastLoginAction->execute($student);

        return redirect()
            ->route('student.dashboard')
            ->with('status', 'تم إنشاء الحساب بنجاح، وحسابك الآن قيد المراجعة.');
    }
}
