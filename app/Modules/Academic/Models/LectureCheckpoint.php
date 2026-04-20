<?php

namespace App\Modules\Academic\Models;

use Database\Factories\LectureCheckpointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LectureCheckpoint extends Model
{
    /** @use HasFactory<LectureCheckpointFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lecture_id',
        'title',
        'position_seconds',
        'sort_order',
        'is_required',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): LectureCheckpointFactory
    {
        return LectureCheckpointFactory::new();
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(LectureProgress::class, 'last_checkpoint_id');
    }
}
