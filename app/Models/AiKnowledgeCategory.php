<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiKnowledgeCategory extends Model
{
    protected $table = 'ai_knowledge_categories';

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon'
    ];

    /**
     * Documents belonging to this category.
     */
    public function docs(): HasMany
    {
        return $this->hasMany(AiKnowledgeDoc::class, 'category', 'name');
    }
}
