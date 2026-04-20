<?php

namespace App\Modules\Academic\Models;

use App\Modules\Students\Models\Student;
use Database\Factories\LectureProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureProgress extends Model
{
    /** @use HasFactory<LectureProgressFactory> */
    use HasFactory;

    protected $table = 'lecture_progress';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'lecture_id',
        'started_at',
        'first_opened_at',
        'last_opened_at',
        'last_position_seconds',
        'consumed_seconds',
        'completion_percent',
        'completed_at',
        'last_checkpoint_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'completed_at' => 'datetime',
            'completion_percent' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): LectureProgressFactory
    {
        return LectureProgressFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function lastCheckpoint(): BelongsTo
    {
        return $this->belongsTo(LectureCheckpoint::class, 'last_checkpoint_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null || (float) $this->completion_percent >= 100;
    }
}
