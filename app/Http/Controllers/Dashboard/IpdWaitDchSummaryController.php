<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdWaitDchSummaryController extends Controller
{
    public function ipd_non_dchsummary(Request $request)
    {
        $budget_year_now = DB::table('budget_year')
            ->where('DATE_END', '>=', date('Y-m-d'))
            ->where('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;
        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first(['DATE_BEGIN', 'DATE_END']);
        $start_date = $year_data->DATE_BEGIN ?? null;
        $end_date = $year_data->DATE_END ?? null;

        $results = DB::connection('hosxp')->select('
        SELECT w.`name` AS ward, i.hn, i.an, id.icd10, a.diag_text_list, d.`name` AS owner_doctor_name,
        i.dchdate, TIMESTAMPDIFF(day, i.dchdate, DATE(NOW())) AS dch_day,
        CASE 
            WHEN (a.diag_text_list = "" OR a.diag_text_list IS NULL) THEN "รอแพทย์สรุป Chart"
            ELSE "รอลงรหัสวินิจฉัยโรค" 
        END AS diag_status,
        CASE 
            WHEN (a.diag_text_list = "" OR a.diag_text_list IS NULL) THEN "non_diagtext"
            ELSE "non_icd10" 
        END AS category
        FROM ipt i
        LEFT JOIN ward w ON w.ward = i.ward 
        LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
        LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
        LEFT JOIN doctor d ON d.`code` = il.doctor
        LEFT JOIN an_stat a ON a.an = i.an
        WHERE i.dchdate BETWEEN ? AND ?        
        AND (
            (a.diag_text_list = "" OR a.diag_text_list IS NULL)
            OR 
            ((a.diag_text_list <> "" AND a.diag_text_list IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL))
        )
        AND i.ward IN ("01","02","03","10")
        GROUP BY i.an
        ORDER BY d.`name`, dch_day DESC', [$start_date, $end_date]);

        $non_diagtext_list = [];
        $non_icd10_list = [];

        foreach ($results as $row) {
            if ($row->category === 'non_diagtext') {
                $non_diagtext_list[] = $row;
            } else {
                $non_icd10_list[] = $row;
            }
        }

        // Summary by doctor for charts
        $summary_sql = '
            SELECT d.`name` AS owner_doctor_name, 
            SUM(CASE WHEN (a.diag_text_list = "" OR a.diag_text_list IS NULL) THEN 1 ELSE 0 END) as non_diagtext_count,
            SUM(CASE WHEN (a.diag_text_list <> "" AND a.diag_text_list IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END) as non_icd10_count
            FROM ipt i
            LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
            LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
            LEFT JOIN doctor d ON d.`code` = il.doctor
            LEFT JOIN an_stat a ON a.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
            AND i.ward IN ("01","02","03","10")
            GROUP BY d.`name`
            HAVING SUM(CASE WHEN (a.diag_text_list = "" OR a.diag_text_list IS NULL) THEN 1 ELSE 0 END) > 0 
            OR SUM(CASE WHEN (a.diag_text_list <> "" AND a.diag_text_list IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END) > 0
            ORDER BY (SUM(CASE WHEN (a.diag_text_list = "" OR a.diag_text_list IS NULL) THEN 1 ELSE 0 END) + SUM(CASE WHEN (a.diag_text_list <> "" AND a.diag_text_list IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END)) DESC';

        $summary_stats = DB::connection('hosxp')->select($summary_sql, [$start_date, $end_date]);

        $chart_data = [
            'doctors' => array_column($summary_stats, 'owner_doctor_name'),
            'non_diagtext' => array_column($summary_stats, 'non_diagtext_count'),
            'non_icd10' => array_column($summary_stats, 'non_icd10_count')
        ];

        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        return view('dashboard.ipd_wait_dchsummary', compact(
            'non_diagtext_list',
            'non_icd10_list',
            'chart_data',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date'
        ));
    }
}
