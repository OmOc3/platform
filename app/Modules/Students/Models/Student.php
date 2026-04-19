<?php

namespace App\Modules\Students\Models;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Enums\StudentSourceType;
use App\Modules\Support\Models\ForumMessage;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Support\Models\Complaint;
use App\Modules\Students\Notifications\StudentResetPasswordNotification;
use App\Shared\Enums\StudentStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'student_number',
        'name',
        'email',
        'phone',
        'parent_phone',
        'governorate',
        'owner_admin_id',
        'grade_id',
        'track_id',
        'center_id',
        'group_id',
        'password',
        'status',
        'source_type',
        'is_azhar',
        'notes',
        'language',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => StudentStatus::class,
            'source_type' => StudentSourceType::class,
            'is_azhar' => 'boolean',
        ];
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StudentResetPasswordNotification($token));
    }

    public function ownerAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'owner_admin_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(EducationalCenter::class, 'center_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EducationalGroup::class, 'group_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class)->latest('created_at');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class)->latest('created_at');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class)->latest('last_activity_at');
    }

    public function forumMessages(): HasMany
    {
        return $this->hasMany(ForumMessage::class, 'author_id')
            ->where('author_type', $this->getMorphClass());
    }

    public function mistakeItems(): HasMany
    {
        return $this->hasMany(MistakeItem::class)->latest('created_at');
    }
}
