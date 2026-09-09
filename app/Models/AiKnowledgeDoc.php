<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AiKnowledgeDoc extends Model
{
    protected $table = 'ai_knowledge_docs';
    protected $fillable = [
        'title',
        'category',
        'filename',
        'file_path',
        'file_size',
        'file_type',
        'status',
        'error_message',
        'chunks_count',
        'embedded_model',
        'uploaded_by'
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(AiKnowledgeChunk::class, 'doc_id')->orderBy('chunk_index', 'asc');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getStoragePathAttribute(): string
    {
        return storage_path('app/public/' . $this->file_path);
    }

    /**
     * Cascade delete: storage file + chunks + record
     */
    public function deleteWithFiles(): bool
    {
        // 1. Delete physical file from storage
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        // 2. Delete all related chunks
        $this->chunks()->delete();

        // 3. Delete this doc record
        return $this->delete();
    }
}
