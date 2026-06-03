<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lend_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่ออุปกรณ์');
            $table->string('category', 50)->default('equipment')->comment('equipment / medicine');
            $table->text('description')->nullable()->comment('รายละเอียด');
            $table->integer('total_qty')->default(1)->comment('จำนวนทั้งหมด');
            $table->char('active', 1)->default('Y')->comment('Y=ใช้งาน N=ปิด');
            $table->integer('sort_order')->default(0)->comment('ลำดับแสดง');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lend_items');
    }
};
