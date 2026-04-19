<?php

namespace App\Modules\Students\Models;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use Database\Factories\MistakeItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MistakeItem extends Model
{
    /** @use HasFactory<MistakeItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'lecture_id',
        'exam_id',
        'question_reference',
        'question_text',
        'correct_answer_snapshot',
        'model_answer_snapshot',
        'explanation',
        'image_path',
        'score_lost',
        'score_meta',
        'source',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'score_meta' => 'array',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): MistakeItemFactory
    {
        return MistakeItemFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
