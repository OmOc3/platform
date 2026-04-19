<?php

namespace App\Modules\Students\Models;

use App\Shared\Enums\StudentStatus;
use Database\Factories\StudentStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentStatusHistory extends Model
{
    /** @use HasFactory<StudentStatusHistoryFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'previous_status',
        'new_status',
        'reason',
        'actor_type',
        'actor_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_status' => StudentStatus::class,
            'new_status' => StudentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): StudentStatusHistoryFactory
    {
        return StudentStatusHistoryFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
