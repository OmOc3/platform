<?php

namespace App\Modules\Academic\Models;

use Database\Factories\LectureSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LectureSection extends Model
{
    /** @use HasFactory<LectureSectionFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'grade_id',
        'track_id',
        'curriculum_section_id',
        'name_ar',
        'name_en',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): LectureSectionFactory
    {
        return LectureSectionFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function curriculumSection(): BelongsTo
    {
        return $this->belongsTo(CurriculumSection::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class)->orderBy('sort_order');
    }
}
