<?php

namespace App\Modules\Identity\Models;

use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketReply;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected string $guard_name = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'job_title',
        'locale',
        'is_active',
        'last_login_at',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id')
            ->where('actor_type', $this->getMorphClass());
    }

    public function forumMessages(): HasMany
    {
        return $this->hasMany(\App\Modules\Support\Models\ForumMessage::class, 'author_id')
            ->where('author_type', $this->getMorphClass());
    }

    public function supportTeams(): BelongsToMany
    {
        return $this->belongsToMany(SupportTeam::class, 'admin_support_team')
            ->withTimestamps();
    }

    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_admin_id');
    }

    public function supportTicketReplies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'author_id')
            ->where('author_type', $this->getMorphClass());
    }
}
