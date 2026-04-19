<?php

namespace App\Modules\Identity\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\Admin\Auth\AdminLoginRequest;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();

        $this->auditLogger->log(
            event: 'identity.admin.logged_in',
            actor: $admin,
            subject: $admin,
            newValues: ['email' => $admin?->email],
        );

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $this->auditLogger->log(
            event: 'identity.admin.logged_out',
            actor: $admin,
            subject: $admin,
        );

        Auth::guard('admin')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
