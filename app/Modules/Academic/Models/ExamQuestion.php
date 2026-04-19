<?php

namespace App\Modules\Academic\Models;

use Database\Factories\ExamQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    /** @use HasFactory<ExamQuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'question_id',
        'sort_order',
        'max_score',
    ];

    protected static function newFactory(): ExamQuestionFactory
    {
        return ExamQuestionFactory::new();
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
