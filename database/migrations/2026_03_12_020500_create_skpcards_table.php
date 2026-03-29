<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('skpcards')) {
            Schema::create('skpcards', function (Blueprint $table) {
                $table->id();
                $table->string('cid', 13);
                $table->string('name');
                $table->date('birthday')->nullable();
                $table->text('address')->nullable();
                $table->string('phone')->nullable();
                $table->date('buy_date');
                $table->date('ex_date');
                $table->string('price');
                $table->string('rcpt')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skpcards');
    }
};
