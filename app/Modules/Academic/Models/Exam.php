<?php

namespace App\Modules\Academic\Models;

use App\Modules\Students\Models\MistakeItem;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lecture_id',
        'grade_id',
        'track_id',
        'title',
        'slug',
        'short_description',
        'long_description',
        'thumbnail_url',
        'price_amount',
        'currency',
        'duration_minutes',
        'question_count',
        'is_active',
        'is_featured',
        'is_free',
        'published_at',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_free' => 'boolean',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): ExamFactory
    {
        return ExamFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function mistakeItems(): HasMany
    {
        return $this->hasMany(MistakeItem::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['sort_order', 'max_score'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('questions.id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class)->latest('started_at');
    }
}
