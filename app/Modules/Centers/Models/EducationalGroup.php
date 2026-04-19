<?php

namespace App\Modules\Centers\Models;

use Database\Factories\EducationalGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationalGroup extends Model
{
    /** @use HasFactory<EducationalGroupFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'center_id',
        'name_ar',
        'name_en',
        'schedule_note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): EducationalGroupFactory
    {
        return EducationalGroupFactory::new();
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(EducationalCenter::class, 'center_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'group_id');
    }
}
