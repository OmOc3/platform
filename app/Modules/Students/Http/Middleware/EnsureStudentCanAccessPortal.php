<?php

namespace App\Modules\Students\Http\Middleware;

use App\Shared\Enums\StudentStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentCanAccessPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user('student');

        if (! $student) {
            return redirect()->route('student.login');
        }

        if (in_array($student->status, [StudentStatus::Blocked, StudentStatus::Refused], true)) {
            auth('student')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('student.login')
                ->with('status', 'تم إيقاف الوصول إلى البوابة لحين مراجعة الإدارة.');
        }

        return $next($request);
    }
}
