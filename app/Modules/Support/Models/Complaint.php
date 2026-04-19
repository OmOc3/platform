<?php

namespace App\Modules\Support\Models;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ComplaintType;
use App\Shared\Enums\ComplaintStatus;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'type',
        'status',
        'content',
        'admin_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ComplaintType::class,
            'status' => ComplaintStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ComplaintFactory
    {
        return ComplaintFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
