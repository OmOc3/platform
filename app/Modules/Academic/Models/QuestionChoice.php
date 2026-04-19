<?php

namespace App\Modules\Academic\Models;

use Database\Factories\QuestionChoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionChoice extends Model
{
    /** @use HasFactory<QuestionChoiceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'question_id',
        'content',
        'is_correct',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): QuestionChoiceFactory
    {
        return QuestionChoiceFactory::new();
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
