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
        Schema::table('users', function (Blueprint $table) {
            $table->char('allow_hosxp_report', 1)->default('N')->comment('รายงาน HOSxP')->after('remember_token');
            $table->char('allow_asset', 1)->default('N')->comment('งานทรัพย์สิน')->after('allow_hosxp_report');
            $table->char('allow_personnel', 1)->default('N')->comment('บุคลากร')->after('allow_asset');
            $table->char('allow_incident', 1)->default('N')->comment('อุบัติการณ์')->after('allow_personnel');
            $table->char('allow_skpcard', 1)->default('N')->comment('บัตรสังฆะประชาร่วมใจ')->after('allow_incident');
            $table->char('allow_audit', 1)->default('N')->comment('ระบบตรวจสอบ')->after('allow_skpcard');
            $table->char('allow_assessment', 1)->default('N')->comment('แบบประเมิน')->after('allow_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allow_hosxp_report', 'allow_asset', 'allow_personnel', 'allow_incident', 'allow_skpcard', 'allow_audit', 'allow_assessment']);
        });
    }
};
