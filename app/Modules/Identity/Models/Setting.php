<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\SettingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'key',
        'label',
        'description',
        'type',
        'value',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'type' => SettingType::class,
        ];
    }

    public function resolvedValue(): mixed
    {
        return match ($this->type) {
            SettingType::Boolean => filter_var($this->value, FILTER_VALIDATE_BOOL),
            SettingType::Number => is_numeric($this->value) ? $this->value + 0 : null,
            SettingType::Json => $this->value ? json_decode($this->value, true) : null,
            default => $this->value,
        };
    }
}
