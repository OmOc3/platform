<?php

namespace App\Modules\Support\Models;

use App\Modules\Support\Enums\ForumAttachmentType;
use Database\Factories\ForumAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumAttachment extends Model
{
    /** @use HasFactory<ForumAttachmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'forum_message_id',
        'type',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumAttachmentType::class,
        ];
    }

    protected static function newFactory(): ForumAttachmentFactory
    {
        return ForumAttachmentFactory::new();
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ForumMessage::class, 'forum_message_id');
    }
}
