<?php

namespace App\Modules\Academic\Models;

use Database\Factories\CurriculumSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumSection extends Model
{
    /** @use HasFactory<CurriculumSectionFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'grade_id',
        'track_id',
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

    protected static function newFactory(): CurriculumSectionFactory
    {
        return CurriculumSectionFactory::new();
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

    public function lectureSections(): HasMany
    {
        return $this->hasMany(LectureSection::class)->orderBy('sort_order');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class)->orderBy('sort_order');
    }
}
