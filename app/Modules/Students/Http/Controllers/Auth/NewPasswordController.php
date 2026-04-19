<?php

namespace App\Modules\Students\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Students\Http\Requests\Auth\StudentResetPasswordRequest;
use App\Modules\Students\Models\Student;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    public function create(): View
    {
        return view('student.auth.reset-password', [
            'request' => request(),
        ]);
    }

    public function store(StudentResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Student $student, string $password): void {
                $student->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($student));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('student.login')
            ->with('status', 'تم تحديث كلمة المرور. يمكنك تسجيل الدخول الآن.');
    }
}
