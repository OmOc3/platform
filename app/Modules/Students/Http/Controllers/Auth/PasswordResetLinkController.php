<?php

namespace App\Modules\Students\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Students\Http\Requests\Auth\StudentForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('student.auth.forgot-password');
    }

    public function store(StudentForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('students')->sendResetLink(
            $request->only('email'),
        );

        return back()->with('status', 'إذا كان البريد مسجلًا لدينا فسيتم إرسال رابط إعادة التعيين.');
    }
}
