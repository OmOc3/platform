<?php

namespace App\Modules\Academic\Models;

use App\Modules\Commerce\Models\PackageItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\MistakeItem;
use App\Shared\Enums\ContentKind;
use Database\Factories\LectureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecture extends Model
{
    /** @use HasFactory<LectureFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'grade_id',
        'track_id',
        'curriculum_section_id',
        'lecture_section_id',
        'title',
        'slug',
        'short_description',
        'long_description',
        'thumbnail_url',
        'type',
        'price_amount',
        'currency',
        'duration_minutes',
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
            'type' => ContentKind::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_free' => 'boolean',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): LectureFactory
    {
        return LectureFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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

    public function lectureSection(): BelongsTo
    {
        return $this->belongsTo(LectureSection::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class)->orderBy('sort_order');
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'item_id')
            ->where('item_type', self::class);
    }

    public function mistakeItems(): HasMany
    {
        return $this->hasMany(MistakeItem::class);
    }
}
