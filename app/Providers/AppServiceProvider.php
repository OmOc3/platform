<?php

namespace App\Providers;

use App\Shared\Contracts\AccessResolver;
use App\Shared\Contracts\AttendanceRecorder;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\CheckoutService;
use App\Shared\Contracts\EntitlementGrantor;
use App\Shared\Contracts\ExamAttemptService;
use App\Shared\Contracts\LectureProgressService;
use App\Shared\Contracts\TicketAssignmentService;
use App\Shared\Services\DatabaseAccessResolver;
use App\Shared\Services\DatabaseAttendanceRecorder;
use App\Shared\Services\DatabaseAuditLogger;
use App\Shared\Services\DatabaseCheckoutService;
use App\Shared\Services\DatabaseEntitlementGrantor;
use App\Shared\Services\DatabaseExamAttemptService;
use App\Shared\Services\DatabaseLectureProgressService;
use App\Shared\Services\DatabaseTicketAssignmentService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccessResolver::class, DatabaseAccessResolver::class);
        $this->app->bind(AttendanceRecorder::class, DatabaseAttendanceRecorder::class);
        $this->app->bind(AuditLogger::class, DatabaseAuditLogger::class);
        $this->app->bind(CheckoutService::class, DatabaseCheckoutService::class);
        $this->app->bind(EntitlementGrantor::class, DatabaseEntitlementGrantor::class);
        $this->app->bind(ExamAttemptService::class, DatabaseExamAttemptService::class);
        $this->app->bind(LectureProgressService::class, DatabaseLectureProgressService::class);
        $this->app->bind(TicketAssignmentService::class, DatabaseTicketAssignmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        View::share('platformBrand', config('platform.brand'));

        Livewire::component('shared.support-widget', \App\Shared\Livewire\SupportWidget::class);
    }
}
