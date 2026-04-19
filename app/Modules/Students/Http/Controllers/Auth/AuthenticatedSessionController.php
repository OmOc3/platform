<?php

namespace App\Modules\Students\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Students\Actions\Auth\UpdateStudentLastLoginAction;
use App\Modules\Students\Http\Requests\Auth\StudentLoginRequest;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly UpdateStudentLastLoginAction $updateStudentLastLoginAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function create(): View
    {
        return view('student.auth.login');
    }

    public function store(StudentLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $student = Auth::guard('student')->user();

        if ($student) {
            $this->updateStudentLastLoginAction->execute($student);

            $this->auditLogger->log(
                event: 'students.logged_in',
                actor: $student,
                subject: $student,
                newValues: ['email' => $student->email],
            );
        }

        return redirect()->intended(route('student.dashboard'));
    }

    public function destroy(): RedirectResponse
    {
        $student = Auth::guard('student')->user();

        if ($student) {
            $this->auditLogger->log(
                event: 'students.logged_out',
                actor: $student,
                subject: $student,
            );
        }

        Auth::guard('student')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
