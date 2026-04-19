<?php

namespace App\Modules\Centers\Models;

use Database\Factories\EducationalCenterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationalCenter extends Model
{
    /** @use HasFactory<EducationalCenterFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'city',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): EducationalCenterFactory
    {
        return EducationalCenterFactory::new();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(EducationalGroup::class, 'center_id');
    }
}
