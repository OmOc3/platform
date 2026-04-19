<?php

namespace App\Providers;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Policies\GradePolicy;
use App\Modules\Academic\Policies\TrackPolicy;
use App\Modules\Support\Models\Complaint;
use App\Modules\Support\Policies\ComplaintPolicy;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\AuditLog;
use App\Modules\Identity\Models\Setting;
use App\Modules\Identity\Policies\AdminPolicy;
use App\Modules\Identity\Policies\AuditLogPolicy;
use App\Modules\Identity\Policies\SettingPolicy;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Policies\StudentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Grade::class => GradePolicy::class,
        Setting::class => SettingPolicy::class,
        Track::class => TrackPolicy::class,
        Student::class => StudentPolicy::class,
        Complaint::class => ComplaintPolicy::class,
    ];

    /**
     * Register services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (mixed $user): bool|null {
            if (! $user instanceof Admin) {
                return null;
            }

            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
