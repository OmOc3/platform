<?php

namespace App\Modules\Support\Models;

use App\Modules\Identity\Models\Admin;
use Database\Factories\SupportTeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTeam extends Model
{
    /** @use HasFactory<SupportTeamFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
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

    protected static function newFactory(): SupportTeamFactory
    {
        return SupportTeamFactory::new();
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_support_team')
            ->withTimestamps();
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(SupportTicketType::class, 'default_team_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
