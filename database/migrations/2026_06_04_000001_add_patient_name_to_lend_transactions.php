<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lend_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('lend_transactions', 'borrower_type')) {
                $table->dropColumn('borrower_type');
            }
            if (!Schema::hasColumn('lend_transactions', 'patient_name')) {
                $table->string('patient_name')->nullable()->after('borrower_name')->comment('ชื่อผู้ป่วย');
            }
            if (!Schema::hasColumn('lend_transactions', 'patient_address')) {
                $table->text('patient_address')->nullable()->after('patient_name')->comment('ที่อยู่ผู้ป่วย');
            }
            if (!Schema::hasColumn('lend_transactions', 'patient_phone')) {
                $table->string('patient_phone', 20)->nullable()->after('patient_address')->comment('เบอร์โทรผู้ป่วย');
            }
            if (!Schema::hasColumn('lend_transactions', 'returner_name')) {
                $table->string('returner_name')->nullable()->after('returned_by')->comment('ชื่อผู้คืน');
            }
            if (!Schema::hasColumn('lend_transactions', 'returner_address')) {
                $table->text('returner_address')->nullable()->after('returner_name')->comment('ที่อยู่ผู้คืน');
            }
            if (!Schema::hasColumn('lend_transactions', 'returner_phone')) {
                $table->string('returner_phone', 20)->nullable()->after('returner_address')->comment('เบอร์โทรผู้คืน');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lend_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('lend_transactions', 'borrower_type')) {
                $table->enum('borrower_type', ['patient', 'other'])->default('other')->comment('ผู้ป่วย/บุคคลทั่วไป')->after('lend_item_id');
            }
            $cols = ['patient_name', 'patient_address', 'patient_phone', 'returner_name', 'returner_address', 'returner_phone'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('lend_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
