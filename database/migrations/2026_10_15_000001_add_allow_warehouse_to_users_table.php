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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'allow_warehouse')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('allow_warehouse', 1)->default('N')->after('allow_asset');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'allow_warehouse')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('allow_warehouse');
            });
        }
    }
};
