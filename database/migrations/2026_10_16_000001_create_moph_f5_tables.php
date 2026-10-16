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
        // 1. DRUGLIST (บัญชียาโรงพยาบาล)
        if (!Schema::hasTable('moph_f5_druglist')) {
            Schema::create('moph_f5_druglist', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->default('');
                $table->string('working_code', 50)->index();
                $table->string('generic_name', 255)->nullable();
                $table->string('gpuid', 50)->nullable();
                $table->string('nlem', 10)->nullable();
                $table->string('product_cat', 10)->nullable();
                $table->string('base_unit', 50)->nullable();
                $table->string('status', 10)->nullable();
                $table->string('date_status', 20)->nullable();
                $table->string('period_rpt', 20)->index();
                $table->string('date_send', 30)->nullable();
                $table->timestamps();

                $table->unique(['hosp_code', 'working_code'], 'uniq_f5_druglist');
                $table->index(['hosp_code', 'working_code', 'period_rpt'], 'idx_druglist_hosp_code_period');
            });
        }

        // 2. PURCHASEPLAN (แผนปฏิบัติการจัดซื้อยา)
        if (!Schema::hasTable('moph_f5_purchaseplan')) {
            Schema::create('moph_f5_purchaseplan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->default('');
                $table->string('yearbudget', 10)->index();
                $table->string('working_code', 50)->index();
                $table->string('generic_name', 255)->nullable();
                $table->string('gpuid', 50)->nullable();
                $table->string('nlem', 10)->nullable();
                $table->double('qty_use_year3', 15, 2)->default(0);
                $table->double('qty_use_year2', 15, 2)->default(0);
                $table->double('qty_use_year1', 15, 2)->default(0);
                $table->double('qty_this_year', 15, 2)->default(0);
                $table->integer('pack_size')->nullable()->default(1);
                $table->string('base_unit', 50)->nullable();
                $table->double('pack_cost', 15, 2)->default(0);
                $table->double('value_this_year', 15, 2)->default(0);
                $table->double('qty_plan_trimes1', 15, 2)->default(0);
                $table->double('qty_plan_trimes2', 15, 2)->default(0);
                $table->double('qty_plan_trimes3', 15, 2)->default(0);
                $table->double('qty_plan_trimes4', 15, 2)->default(0);
                $table->string('period_rpt', 20)->index();
                $table->string('date_send', 30)->nullable();
                $table->timestamps();

                $table->unique(['hosp_code', 'yearbudget', 'working_code', 'period_rpt'], 'uniq_f5_purchaseplan');
                $table->index(['hosp_code', 'working_code', 'period_rpt'], 'idx_plan_hosp_code_period');
            });
        }

        // 3. RECEIPT (รับยาเข้าคลัง)
        if (!Schema::hasTable('moph_f5_receipt')) {
            Schema::create('moph_f5_receipt', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->default('');
                $table->string('working_code', 50)->index();
                $table->string('trade_name', 255)->nullable();
                $table->string('tpuid', 50)->nullable();
                $table->string('vendor_name', 255)->nullable();
                $table->string('vendor_tax_id', 50)->nullable();
                $table->double('qty_rcv', 15, 2)->default(0);
                $table->integer('pack_size')->nullable()->default(1);
                $table->string('base_unit', 50)->nullable();
                $table->double('pack_cost', 15, 2)->default(0);
                $table->double('total_value', 15, 2)->default(0);
                $table->string('lot_no', 100)->nullable();
                $table->string('expire_date', 20)->nullable()->index();
                $table->string('rcv_no', 100)->nullable()->index();
                $table->string('po_no', 100)->nullable();
                $table->string('cnt_no', 100)->nullable();
                $table->string('date_rcv', 20)->nullable()->index();
                $table->string('buy_method_id', 10)->nullable();
                $table->string('co_purchase_id', 10)->nullable();
                $table->string('rcv_flag', 10)->nullable();
                $table->string('period_rpt', 20)->index();
                $table->string('date_send', 30)->nullable();
                $table->timestamps();

                $table->unique(['hosp_code', 'rcv_no', 'working_code', 'lot_no', 'expire_date', 'rcv_flag'], 'uniq_f5_receipt');
                $table->index(['hosp_code', 'working_code', 'period_rpt'], 'idx_rcv_hosp_code_period');
            });
        }

        // 4. DISTRIBUTION (จ่ายยาออกจากคลัง)
        if (!Schema::hasTable('moph_f5_distribution')) {
            Schema::create('moph_f5_distribution', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->default('');
                $table->string('working_code', 50)->index();
                $table->string('trade_name', 255)->nullable();
                $table->string('tpuid', 50)->nullable();
                $table->double('qty_dis', 15, 2)->default(0);
                $table->integer('pack_size')->nullable()->default(1);
                $table->string('base_unit', 50)->nullable();
                $table->double('value', 15, 2)->default(0);
                $table->string('dis_dept_group', 10)->nullable()->index();
                $table->string('period_rpt', 20)->index();
                $table->string('date_send', 30)->nullable();
                $table->timestamps();

                $table->unique(['hosp_code', 'working_code', 'trade_name', 'pack_size', 'dis_dept_group', 'period_rpt'], 'uniq_f5_distribution');
                $table->index(['hosp_code', 'working_code', 'period_rpt'], 'idx_dis_hosp_code_period');
            });
        }

        // 5. INVENTORY (คงคลัง)
        if (!Schema::hasTable('moph_f5_inventory')) {
            Schema::create('moph_f5_inventory', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->default('');
                $table->string('working_code', 50)->index();
                $table->string('trade_name', 255)->nullable();
                $table->string('tpuid', 50)->nullable();
                $table->string('vendor_name', 255)->nullable();
                $table->string('vendor_tax_id', 50)->nullable();
                $table->double('qty_onhand', 15, 2)->default(0);
                $table->integer('pack_size')->nullable()->default(1);
                $table->string('base_unit', 50)->nullable();
                $table->double('pack_cost', 15, 2)->default(0);
                $table->double('value_onhand', 15, 2)->default(0);
                $table->string('lot_no', 100)->nullable();
                $table->string('expire_date', 20)->nullable()->index();
                $table->string('date_onhand', 20)->nullable()->index();
                $table->string('date_send', 30)->nullable();
                $table->timestamps();

                $table->unique(['hosp_code', 'working_code', 'lot_no', 'expire_date', 'date_onhand'], 'uniq_f5_inventory');
                $table->index(['hosp_code', 'working_code', 'date_onhand'], 'idx_inv_hosp_code_date');
            });
        }

        // 6. UPLOAD LOGS (ประวัติการนำเข้าไฟล์)
        if (!Schema::hasTable('moph_f5_upload_logs')) {
            Schema::create('moph_f5_upload_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hosp_code', 20)->nullable();
                $table->string('period_rpt', 20)->nullable();
                $table->string('source', 50)->default('web_upload');
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->integer('files_count')->default(0);
                $table->integer('rows_imported')->default(0);
                $table->string('status', 20)->default('success');
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moph_f5_upload_logs');
        Schema::dropIfExists('moph_f5_inventory');
        Schema::dropIfExists('moph_f5_distribution');
        Schema::dropIfExists('moph_f5_receipt');
        Schema::dropIfExists('moph_f5_purchaseplan');
        Schema::dropIfExists('moph_f5_druglist');
    }
};
