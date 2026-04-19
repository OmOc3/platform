<?php

namespace App\Modules\Support\Models;

use Database\Factories\ForumMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumMessage extends Model
{
    /** @use HasFactory<ForumMessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'forum_thread_id',
        'author_type',
        'author_id',
        'body',
        'is_staff_reply',
    ];

    protected function casts(): array
    {
        return [
            'is_staff_reply' => 'boolean',
        ];
    }

    protected static function newFactory(): ForumMessageFactory
    {
        return ForumMessageFactory::new();
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ForumAttachment::class);
    }
}
