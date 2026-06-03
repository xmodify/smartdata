<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LendTransaction extends Model
{
    protected $fillable = [
        'lend_item_id',
        'borrower_type',
        'hn',
        'borrower_name',
        'borrower_address',
        'borrower_phone',
        'borrow_date',
        'due_date',
        'return_date',
        'return_time',
        'qty',
        'deposit_amount',
        'deposit_receipt_no',
        'note',
        'status',
        'created_by',
        'returned_by',
        'returned_note',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'borrow_date'    => 'date',
        'due_date'       => 'date',
        'return_date'    => 'date',
        'cancelled_at'   => 'datetime',
        'deposit_amount' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────────
    public function lendItem()
    {
        return $this->belongsTo(LendItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ─── Methods ─────────────────────────────────────────────────
    /**
     * ตรวจสอบว่าเกินกำหนดคืนไหม
     */
    public function isOverdue(): bool
    {
        return $this->status === 'borrowed'
            && $this->due_date !== null
            && $this->due_date->isPast()
            && !$this->due_date->isToday();
    }

    /**
     * สถานะที่แสดงจริง (รวม overdue ที่คำนวณอัตโนมัติ)
     */
    public function getEffectiveStatus(): string
    {
        if ($this->status === 'borrowed' && $this->isOverdue()) {
            return 'overdue';
        }
        return $this->status;
    }

    /**
     * Label ภาษาไทย + Bootstrap badge class
     */
    public function getStatusBadge(): array
    {
        return match ($this->getEffectiveStatus()) {
            'borrowed'  => ['label' => 'กำลังยืม',  'class' => 'bg-primary'],
            'overdue'   => ['label' => 'เกินกำหนด', 'class' => 'bg-danger'],
            'returned'  => ['label' => 'คืนแล้ว',   'class' => 'bg-success'],
            'cancelled' => ['label' => 'ยกเลิก',    'class' => 'bg-secondary'],
            default     => ['label' => 'ไม่ทราบ',   'class' => 'bg-light text-dark'],
        };
    }
}
