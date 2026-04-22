<?php

namespace App\Modules\Support\Models;

use Database\Factories\SupportTicketReplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupportTicketReply extends Model
{
    /** @use HasFactory<SupportTicketReplyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'support_ticket_id',
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

    protected static function newFactory(): SupportTicketReplyFactory
    {
        return SupportTicketReplyFactory::new();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }
}
