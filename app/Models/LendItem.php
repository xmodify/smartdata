<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LendItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'total_qty',
        'active',
        'sort_order',
    ];

    public function transactions()
    {
        return $this->hasMany(LendTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 'Y');
    }

    public function scopeEquipment($query)
    {
        return $query->where('category', 'equipment');
    }

    /**
     * จำนวนที่ยังคงให้ยืมได้ (total - กำลังยืมอยู่)
     */
    public function availableQty(): int
    {
        $borrowed = $this->transactions()
            ->where('status', 'borrowed')
            ->sum('qty');
        return max(0, $this->total_qty - $borrowed);
    }
}
