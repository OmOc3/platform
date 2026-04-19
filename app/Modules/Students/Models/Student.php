<?php

namespace App\Modules\Students\Models;

use App\Shared\Enums\StudentStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'password',
        'status',
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
        ];
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }
}
