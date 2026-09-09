<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiKnowledgeChunk extends Model
{
    protected $table = 'ai_knowledge_chunks';
    protected $fillable = [
        'doc_id',
        'chunk_index',
        'content',
        'embedding',
        'embedding_dim',
        'page_number'
    ];

    protected $casts = [
        'embedding' => 'array'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeDoc::class, 'doc_id');
    }
}
