<?php

namespace App\Modules\Support\Models;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use Database\Factories\ForumThreadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model
{
    /** @use HasFactory<ForumThreadFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'title',
        'status',
        'visibility',
        'last_activity_at',
        'answered_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumThreadStatus::class,
            'visibility' => ForumVisibility::class,
            'last_activity_at' => 'datetime',
            'answered_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ForumThreadFactory
    {
        return ForumThreadFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ForumMessage::class)->oldest('created_at');
    }

    public function firstMessage(): HasOne
    {
        return $this->hasOne(ForumMessage::class)->oldestOfMany();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ForumMessage::class)->latestOfMany();
    }
}
