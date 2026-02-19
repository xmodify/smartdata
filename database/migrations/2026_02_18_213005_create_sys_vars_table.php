<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sys_var')) {
            Schema::create('sys_var', function (Blueprint $table) {
                $table->string('sys_name')->primary();
                $table->string('sys_name_th')->nullable();
                $table->text('sys_value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_var');
    }
};
