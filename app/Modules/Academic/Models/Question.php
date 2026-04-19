<?php

namespace App\Modules\Academic\Models;

use App\Shared\Enums\QuestionType;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'prompt',
        'explanation',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): QuestionFactory
    {
        return QuestionFactory::new();
    }

    public function choices(): HasMany
    {
        return $this->hasMany(QuestionChoice::class)->orderBy('sort_order')->orderBy('id');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot(['sort_order', 'max_score'])
            ->withTimestamps();
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }
}
