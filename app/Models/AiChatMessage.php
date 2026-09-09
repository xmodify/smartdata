<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $table = 'ai_chat_messages';
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'message_type',
        'generated_sql',
        'query_result',
        'target_db',
        'sources'
    ];

    protected $casts = [
        'query_result' => 'array',
        'sources' => 'array'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }
}
