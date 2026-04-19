<?php

namespace App\Modules\Academic\Models;

use Database\Factories\GradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
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

    protected static function newFactory(): GradeFactory
    {
        return GradeFactory::new();
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
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
