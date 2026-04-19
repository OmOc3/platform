<?php

namespace App\Modules\Academic\Models;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'grade_id',
        'name_ar',
        'name_en',
        'code',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): TrackFactory
    {
        return TrackFactory::new();
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function curriculumSections(): HasMany
    {
        return $this->hasMany(CurriculumSection::class)->orderBy('sort_order');
    }

    public function lectureSections(): HasMany
    {
        return $this->hasMany(LectureSection::class)->orderBy('sort_order');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class)->orderBy('sort_order');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class)->orderBy('sort_order');
    }
}
