<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lend_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lend_item_id')->constrained('lend_items')->comment('อุปกรณ์');

            // ข้อมูลผู้ยืม
            $table->enum('borrower_type', ['patient', 'other'])->default('other')->comment('ผู้ป่วย/บุคคลทั่วไป');
            $table->string('hn', 20)->nullable()->comment('HN จาก HOSxP');
            $table->string('borrower_name')->comment('ชื่อผู้ยืม');
            $table->text('borrower_address')->nullable()->comment('ที่อยู่ผู้ยืม');
            $table->string('borrower_phone', 20)->nullable()->comment('เบอร์โทรผู้ยืม');

            // วันที่ยืม-คืน
            $table->date('borrow_date')->comment('วันที่ยืม');
            $table->date('due_date')->nullable()->comment('วันกำหนดคืน');
            $table->date('return_date')->nullable()->comment('วันที่คืนจริง');
            $table->time('return_time')->nullable()->comment('เวลาที่คืนจริง');

            // รายละเอียดการยืม
            $table->integer('qty')->default(1)->comment('จำนวนที่ยืม');
            $table->decimal('deposit_amount', 10, 2)->nullable()->comment('ค่ามัดจำ');
            $table->string('deposit_receipt_no', 50)->nullable()->comment('เลขใบเสร็จมัดจำ');
            $table->text('note')->nullable()->comment('หมายเหตุ');

            // สถานะ
            $table->enum('status', ['borrowed', 'returned', 'cancelled'])->default('borrowed')->comment('สถานะ');

            // ผู้ดำเนินการ
            $table->unsignedBigInteger('created_by')->comment('ผู้จ่าย/ผู้บันทึก');
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedBigInteger('returned_by')->nullable()->comment('ผู้รับคืน');
            $table->foreign('returned_by')->references('id')->on('users');
            $table->text('returned_note')->nullable()->comment('หมายเหตุการคืน');

            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('ผู้ยกเลิก');
            $table->foreign('cancelled_by')->references('id')->on('users');
            $table->timestamp('cancelled_at')->nullable()->comment('วันเวลาที่ยกเลิก');
            $table->text('cancelled_reason')->nullable()->comment('เหตุผลที่ยกเลิก');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lend_transactions');
    }
};
