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
        if (Schema::hasTable('license_programs')) {
            Schema::table('license_programs', function (Blueprint $table) {
                if (!Schema::hasColumn('license_programs', 'github_token')) {
                    $table->string('github_token', 255)->nullable()->after('language');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('license_programs')) {
            Schema::table('license_programs', function (Blueprint $table) {
                if (Schema::hasColumn('license_programs', 'github_token')) {
                    $table->dropColumn('github_token');
                }
            });
        }
    }
};
