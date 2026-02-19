<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Display the HOSxP Stats Index.
     */
    public function index()
    {
        return view('hosxp.stats.index');
    }

    public function top20_opd(Request $request)
    {
        $title = '20 อันดับโรค (Primary Diagnosis) ผู้ป่วยนอก';
        return $this->render_report($request, 'top20_opd', $title);
    }

    public function group_504(Request $request)
    {
        $title = 'กลุ่มสาเหตุ (21 กลุ่มโรค) (รง.504) ผู้ป่วยนอก';
        return $this->render_report($request, 'group_504', $title);
    }

    public function top20_ipd(Request $request)
    {
        $title = '20 อันดับโรค (Primary Diagnosis) ผู้ป่วยใน';
        return $this->render_report($request, 'top20_ipd', $title);
    }

    public function group_505(Request $request)
    {
        $title = 'กลุ่มโรค (75 กลุ่มโรค) (รง.505) ผู้ป่วยใน';
        return $this->render_report($request, 'group_505', $title);
    }

    public function group_506(Request $request)
    {
        $title = 'กลุ่มโรคที่ต้องเฝ้าระวัง (รง.506)';
        return $this->render_report($request, 'group_506', $title);
    }

    public function refer_out(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out';
        return $this->render_report($request, 'refer_out', $title);
    }

    public function refer_out_4h(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 4 ชม.หลัง Admit';
        return $this->render_report($request, 'refer_out_4h', $title);
    }

    public function refer_out_24h(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 24 ชม.หลัง Admit';
        return $this->render_report($request, 'refer_out_24h', $title);
    }

    public function refer_out_top20(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out 30 อันดับโรค (Primary Diagnosis)';
        return $this->render_report($request, 'refer_out_top20', $title);
    }

    public function death(Request $request)
    {
        $title = 'รายงานการเสียชีวิต';
        return $this->render_report($request, 'death', $title);
    }

    public function death_top20(Request $request)
    {
        $title = '20 อันดับโรค (Primary Diagnosis) การเสียชีวิต';
        return $this->render_report($request, 'death_top20', $title);
    }

    /**
     * Helper to render report with budget year selection.
     */
    private function render_report(Request $request, $type, $title)
    {
        $budget_year_select = \DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = \DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        return view('hosxp.stats.' . $type, compact('type', 'title', 'budget_year_select', 'budget_year'));
    }
}
