<?php

namespace App\Modules\Academic\Models;

use App\Modules\Students\Models\Student;
use App\Shared\Enums\ExamAttemptStatus;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'student_id',
        'status',
        'started_at',
        'submitted_at',
        'graded_at',
        'total_questions',
        'answered_questions',
        'correct_answers_count',
        'total_score',
        'max_score',
        'attempt_number',
        'time_limit_snapshot',
        'result_meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExamAttemptStatus::class,
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'result_meta' => 'array',
        ];
    }

    protected static function newFactory(): ExamAttemptFactory
    {
        return ExamAttemptFactory::new();
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class)->orderBy('question_id');
    }

    public function expiresAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->time_limit_snapshot === null) {
            return null;
        }

        return $this->started_at?->copy()->addMinutes($this->time_limit_snapshot);
    }
}
