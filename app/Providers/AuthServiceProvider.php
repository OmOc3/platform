<?php

namespace App\Providers;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Policies\CurriculumSectionPolicy;
use App\Modules\Academic\Policies\ExamPolicy;
use App\Modules\Academic\Policies\GradePolicy;
use App\Modules\Academic\Policies\LecturePolicy;
use App\Modules\Academic\Policies\LectureSectionPolicy;
use App\Modules\Academic\Policies\TrackPolicy;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Policies\BookPolicy;
use App\Modules\Commerce\Policies\EntitlementPolicy;
use App\Modules\Commerce\Policies\OrderPolicy;
use App\Modules\Commerce\Policies\PackagePolicy;
use App\Modules\Support\Models\Complaint;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Support\Policies\ComplaintPolicy;
use App\Modules\Support\Policies\ForumThreadPolicy;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\AuditLog;
use App\Modules\Identity\Models\Setting;
use App\Modules\Identity\Policies\AdminPolicy;
use App\Modules\Identity\Policies\AuditLogPolicy;
use App\Modules\Identity\Policies\SettingPolicy;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Policies\MistakeItemPolicy;
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
        Book::class => BookPolicy::class,
        Entitlement::class => EntitlementPolicy::class,
        Order::class => OrderPolicy::class,
        CurriculumSection::class => CurriculumSectionPolicy::class,
        Exam::class => ExamPolicy::class,
        ForumThread::class => ForumThreadPolicy::class,
        Grade::class => GradePolicy::class,
        Lecture::class => LecturePolicy::class,
        LectureSection::class => LectureSectionPolicy::class,
        MistakeItem::class => MistakeItemPolicy::class,
        Package::class => PackagePolicy::class,
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
