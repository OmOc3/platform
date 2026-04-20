<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Enums\ContentKind;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StudentLectureCatalogQuery
{
    public function __construct(private readonly AccessResolver $accessResolver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function dataFor(Student $student, Request $request): array
    {
        $tab = $request->string('tab')->toString();
        $tab = in_array($tab, ['review', 'exam'], true) ? $tab : 'lecture';
        $scope = $request->string('scope')->toString();
        $scope = in_array($scope, ['free'], true) ? $scope : null;
        $curriculumSection = $request->integer('curriculum_section');
        $lectureSection = $request->integer('lecture_section');

        $sections = CurriculumSection::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn ($query) => $query->where(function ($builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $lectureSections = \App\Modules\Academic\Models\LectureSection::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn ($query) => $query->where(function ($builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }))
            ->when($curriculumSection > 0, fn ($query) => $query->where('curriculum_section_id', $curriculumSection))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($tab === 'exam') {
            $items = $this->examBaseQuery($student)
                ->with(['lecture', 'grade', 'track'])
                ->when($lectureSection > 0, fn ($query) => $query->whereHas('lecture', fn ($builder) => $builder->where('lecture_section_id', $lectureSection)))
                ->where('is_active', true)
                ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Exam $exam): array => [
                    'resource' => $exam,
                    'access' => $this->accessResolver->resolveState($student, $exam),
                ]);
        } else {
            $type = $tab === 'review' ? ContentKind::Review : ContentKind::Lecture;

            $items = $this->lectureBaseQuery($student)
                ->with([
                    'grade',
                    'track',
                    'curriculumSection',
                    'lectureSection',
                    'product',
                    'progressRecords' => fn ($query) => $query
                        ->where('student_id', $student->id)
                        ->with('lastCheckpoint'),
                ])
                ->where('type', $type->value)
                ->when($curriculumSection > 0, fn ($query) => $query->where('curriculum_section_id', $curriculumSection))
                ->when($lectureSection > 0, fn ($query) => $query->where('lecture_section_id', $lectureSection))
                ->where('is_active', true)
                ->when($scope === 'free', fn ($query) => $query->where('is_free', true))
                ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Lecture $lecture): array => [
                    'resource' => $lecture,
                    'access' => $this->accessResolver->resolveState($student, $lecture),
                    'progress' => $this->progressSummary($lecture->progressRecords->first()),
                ]);
        }

        return [
            'tab' => $tab,
            'scope' => $scope,
            'items' => $items,
            'curriculumSections' => $sections,
            'lectureSections' => $lectureSections,
            'overview' => [
                'lectures' => $this->lectureBaseQuery($student)
                    ->where('type', ContentKind::Lecture->value)
                    ->where('is_active', true)
                    ->count(),
                'reviews' => $this->lectureBaseQuery($student)
                    ->where('type', ContentKind::Review->value)
                    ->where('is_active', true)
                    ->count(),
                'exams' => $this->examBaseQuery($student)
                    ->where('is_active', true)
                    ->count(),
                'free_lectures' => $this->lectureBaseQuery($student)
                    ->where('type', ContentKind::Lecture->value)
                    ->where('is_active', true)
                    ->where('is_free', true)
                    ->count(),
            ],
        ];
    }

    /**
     * @return array{status: string, label: string, percent: int}
     */
    private function progressSummary(?LectureProgress $progress): array
    {
        if (! $progress instanceof LectureProgress) {
            return [
                'status' => 'not_started',
                'label' => 'لم تبدأ',
                'percent' => 0,
            ];
        }

        $percent = (int) round((float) $progress->completion_percent);

        if ($progress->completed_at !== null || $percent >= 100) {
            return [
                'status' => 'completed',
                'label' => 'مكتمل',
                'percent' => 100,
            ];
        }

        if ($percent > 0) {
            return [
                'status' => 'in_progress',
                'label' => $percent.'% مكتمل',
                'percent' => $percent,
            ];
        }

        return [
            'status' => 'started',
            'label' => 'بدأت',
            'percent' => 0,
        ];
    }

    private function lectureBaseQuery(Student $student): Builder
    {
        return Lecture::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn (Builder $query) => $query->where(function (Builder $builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }));
    }

    private function examBaseQuery(Student $student): Builder
    {
        return Exam::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn (Builder $query) => $query->where(function (Builder $builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }));
    }
}
