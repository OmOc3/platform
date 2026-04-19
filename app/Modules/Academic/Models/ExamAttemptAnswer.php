<?php

namespace App\Modules\Academic\Models;

use Database\Factories\ExamAttemptAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    /** @use HasFactory<ExamAttemptAnswerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'selected_answer',
        'answer_payload',
        'is_correct',
        'awarded_score',
        'max_score',
        'answer_meta',
    ];

    protected function casts(): array
    {
        return [
            'answer_payload' => 'array',
            'answer_meta' => 'array',
            'is_correct' => 'boolean',
        ];
    }

    protected static function newFactory(): ExamAttemptAnswerFactory
    {
        return ExamAttemptAnswerFactory::new();
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
