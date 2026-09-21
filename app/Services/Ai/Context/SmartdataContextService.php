<?php

namespace App\Services\Ai\Context;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartdataContextService
{
    /**
     * Check if Smartdata (MySQL) connection is accessible
     */
    public function isSmartdataConnected(): bool
    {
        try {
            DB::connection('mysql')->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get live Smartdata context based on user query
     */
    public function getContext(string $query): ?array
    {
        try {
            if (!$this->isSmartdataConnected()) {
                return null;
            }

            // Optional live lookups for users, lend items, knowledge categories
            $contextBlocks = [];
            $sources = [];

            if (preg_match('/(คลังความรู้|หมวดหมู่ความรู้|หมวดหมู่เอกสาร)/iu', $query)) {
                $categories = DB::connection('mysql')->table('ai_knowledge_docs')
                    ->distinct()
                    ->pluck('category')
                    ->filter()
                    ->toArray();

                if (!empty($categories)) {
                    $contextBlocks[] = "-- หมวดหมู่เอกสารคลังความรู้ที่มีในระบบ: " . implode(', ', $categories);
                    $sources[] = 'ตาราง ai_knowledge_docs (SmartData)';
                }
            }

            if (empty($contextBlocks)) {
                return null;
            }

            return [
                'text' => implode("\n\n", $contextBlocks),
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            Log::warning("SmartdataContextService getContext error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Complete SmartData Schema Dictionary & Domain Rules
     */
    public function getSchemaContext(string $query = ''): string
    {
        return "
-- ฐานข้อมูล SmartData (ระบบจัดการภายใน)
- users: ผู้ใช้งานระบบ (id, name, username, email, role, allow_copilot, active, created_at)
- ai_knowledge_docs: เอกสารคลังความรู้ (id, title, category, file_type, file_size, status, chunks_count, created_at)
- lend_items: รายการอุปกรณ์ให้ยืม (id, item_code, item_name, category, status, total_qty, available_qty)
- lend_transactions: รายการยืม-คืน (id, item_id, borrower_name, borrow_date, return_date, status)
- customer_complains: ข้อร้องเรียน (id, topic, detail, status, created_at)
";
    }
}
