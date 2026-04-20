<?php

namespace App\Modules\Academic\Models;

use App\Shared\Enums\LectureAssetKind;
use Database\Factories\LectureAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureAsset extends Model
{
    /** @use HasFactory<LectureAssetFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lecture_id',
        'kind',
        'title',
        'url',
        'body',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'kind' => LectureAssetKind::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): LectureAssetFactory
    {
        return LectureAssetFactory::new();
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }
}
