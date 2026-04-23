<?php

namespace App\Modules\Support\Models;

use Database\Factories\SupportTicketTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicketType extends Model
{
    /** @use HasFactory<SupportTicketTypeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'default_team_id',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): SupportTicketTypeFactory
    {
        return SupportTicketTypeFactory::new();
    }

    public function defaultTeam(): BelongsTo
    {
        return $this->belongsTo(SupportTeam::class, 'default_team_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
