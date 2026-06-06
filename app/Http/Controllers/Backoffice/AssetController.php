<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AssetController extends Controller
{
    public function index()
    {
        $categoryIds = [5, 6, 7, 8, 9, 10, 11, 12, 17, 18, 20, 21];

        // Fetch only assets with normal status (STATUS_ID = 1) and not deleted
        $categories = DB::connection('backoffice')->select('
            SELECT d.DECLINE_ID, d.DECLINE_NAME, COUNT(a.ARTICLE_ID) as asset_count
            FROM supplies_decline d
            LEFT JOIN asset_article a ON a.DECLINE_ID = d.DECLINE_ID AND a.STATUS_ID = 1 AND a.STATUS_DELETE = "false"
            WHERE d.DECLINE_ID IN (' . implode(',', $categoryIds) . ')
            GROUP BY d.DECLINE_ID, d.DECLINE_NAME
            ORDER BY FIELD(d.DECLINE_ID, ' . implode(',', $categoryIds) . ')
        ');

        return view('backoffice.asset.index', compact('categories'));
    }

    public function show($decline_id)
    {
        $category = DB::connection('backoffice')->selectOne('
            SELECT * FROM supplies_decline WHERE DECLINE_ID = ?
        ', [$decline_id]);

        if (!$category) {
            abort(404, 'Category not found');
        }

        // Fetch all assets for this category regardless of status
        $assets = DB::connection('backoffice')->select('
            SELECT a.ARTICLE_ID, a.SUP_FSN, s.SUP_NAME, a.ARTICLE_NUM, a.ARTICLE_NAME, sb.BRAND_NAME, sm.MODEL_NAME, sv.VENDOR_NAME,
            a.ARTICLE_PROP, a.RECEIVE_DATE, PRICE_PER_UNIT, st.BUY_NAME, sbg.BUDGET_NAME, a.STATUS_ID,
            COALESCE(ast.STATUS_NAME, "ปกติ") AS STATUS_NAME,
            IF(ds.HR_DEPARTMENT_SUB_SUB_NAME IS NULL, "รพ.หัวตะพาน", ds.HR_DEPARTMENT_SUB_SUB_NAME) AS HR_DEPARTMENT_SUB_SUB_NAME 
            FROM asset_article a
            LEFT JOIN supplies s ON s.SUP_FSN_NUM = a.SUP_FSN
            LEFT JOIN supplies_brand sb ON sb.BRAND_ID = a.BRAND_ID
            LEFT JOIN supplies_model sm ON sm.MODEL_ID = a.MODEL_ID
            LEFT JOIN supplies_vendor sv ON sv.VENDOR_ID = a.VENDOR_ID
            LEFT JOIN supplies_buy st ON st.BUY_ID = a.BUY_ID
            LEFT JOIN supplies_budget sbg ON sbg.BUDGET_ID = a.BUDGET_ID
            LEFT JOIN hrd_department_sub_sub ds ON ds.HR_DEPARTMENT_SUB_SUB_ID = a.DEP_ID
            LEFT JOIN hrd_person hr ON hr.ID = a.PERSON_ID
            LEFT JOIN asset_status ast ON ast.STATUS_ID = a.STATUS_ID
            WHERE a.DECLINE_ID = ? AND a.STATUS_DELETE = "false"
            GROUP BY a.ARTICLE_ID 
            ORDER BY a.SUP_FSN, a.ARTICLE_NUM + 0
        ', [$decline_id]);

        // Fetch software details for assets in this category
        $assetSoftware = [];
        if ($decline_id == 18) {
            $softwares = DB::connection('backoffice')->select('
                SELECT al.ARTICLE_ID, al.CARE_LIST_NAME
                FROM asset_care_list al
                INNER JOIN asset_article a ON a.ARTICLE_ID = al.ARTICLE_ID
                WHERE a.DECLINE_ID = ? AND a.STATUS_DELETE = "false"
            ', [$decline_id]);
            
            foreach ($softwares as $sw) {
                $assetSoftware[$sw->ARTICLE_ID][] = $sw->CARE_LIST_NAME;
            }
        }

        // Group assets by status
        $groupedAssets = [];
        foreach ($assets as $asset) {
            $asset->age_string = self::calculateAge($asset->RECEIVE_DATE);
            $asset->thai_receive_date = self::formatThaiDate($asset->RECEIVE_DATE, 'short');
            
            // Attach software names list
            $asset->software_list = isset($assetSoftware[$asset->ARTICLE_ID]) ? implode(', ', $assetSoftware[$asset->ARTICLE_ID]) : '';
            
            $statusId = $asset->STATUS_ID ?: '1';
            $statusName = $asset->STATUS_NAME;
            
            if (!isset($groupedAssets[$statusId])) {
                $groupedAssets[$statusId] = [
                    'id' => $statusId,
                    'name' => $statusName,
                    'items' => []
                ];
            }
            $groupedAssets[$statusId]['items'][] = $asset;
        }

        // Sort grouped assets so that 'ปกติ' (1) is always first if exists
        ksort($groupedAssets);

        $year = date('Y');
        $month = date('m');
        $fiscalYear = ($month >= 10) ? $year + 1 : $year;
        $fiscalYearThai = $fiscalYear + 543;

        return view('backoffice.asset.show', compact('groupedAssets', 'category', 'fiscalYearThai'));
    }

    public function pdf(Request $request, $decline_id)
    {
        ini_set('memory_limit', '1024M');
        
        $status_id = $request->query('status_id', 1);

        $category = DB::connection('backoffice')->selectOne('
            SELECT * FROM supplies_decline WHERE DECLINE_ID = ?
        ', [$decline_id]);

        if (!$category) {
            abort(404);
        }

        $status = DB::connection('backoffice')->selectOne('
            SELECT * FROM asset_status WHERE STATUS_ID = ?
        ', [$status_id]);
        $statusName = $status ? $status->STATUS_NAME : 'ปกติ';

        $assets = DB::connection('backoffice')->select('
            SELECT a.ARTICLE_ID, a.SUP_FSN, s.SUP_NAME, a.ARTICLE_NUM, a.ARTICLE_NAME, sb.BRAND_NAME, sm.MODEL_NAME, sv.VENDOR_NAME,
            a.ARTICLE_PROP, a.RECEIVE_DATE, PRICE_PER_UNIT, st.BUY_NAME, sbg.BUDGET_NAME,
            IF(ds.HR_DEPARTMENT_SUB_SUB_NAME IS NULL, "รพ.หัวตะพาน", ds.HR_DEPARTMENT_SUB_SUB_NAME) AS HR_DEPARTMENT_SUB_SUB_NAME 
            FROM asset_article a
            LEFT JOIN supplies s ON s.SUP_FSN_NUM = a.SUP_FSN
            LEFT JOIN supplies_brand sb ON sb.BRAND_ID = a.BRAND_ID
            LEFT JOIN supplies_model sm ON sm.MODEL_ID = a.MODEL_ID
            LEFT JOIN supplies_vendor sv ON sv.VENDOR_ID = a.VENDOR_ID
            LEFT JOIN supplies_buy st ON st.BUY_ID = a.BUY_ID
            LEFT JOIN supplies_budget sbg ON sbg.BUDGET_ID = a.BUDGET_ID
            LEFT JOIN hrd_department_sub_sub ds ON ds.HR_DEPARTMENT_SUB_SUB_ID = a.DEP_ID
            LEFT JOIN hrd_person hr ON hr.ID = a.PERSON_ID
            WHERE a.DECLINE_ID = ? AND a.STATUS_ID = ? AND a.STATUS_DELETE = "false"
            GROUP BY a.ARTICLE_ID 
            ORDER BY a.SUP_FSN, a.ARTICLE_NUM + 0
        ', [$decline_id, $status_id]);

        foreach ($assets as $asset) {
            $asset->age_string = self::calculateAge($asset->RECEIVE_DATE);
            $asset->thai_receive_date = self::formatThaiDate($asset->RECEIVE_DATE, 'short');
        }

        $year = date('Y');
        $month = date('m');
        $fiscalYear = ($month >= 10) ? $year + 1 : $year;
        $fiscalYearThai = $fiscalYear + 543;

        $pdf = Pdf::loadView('backoffice.asset.pdf', compact('assets', 'category', 'statusName', 'fiscalYearThai'))
            ->setPaper('A4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->stream(strtolower(str_replace(' ', '_', $category->DECLINE_NAME)) . '_report.pdf');
    }

    public function getSoftware($article_id)
    {
        $data = DB::connection('backoffice')->select('
            SELECT a.ARTICLE_NUM, a.ARTICLE_NAME, al.CARE_LIST_NAME
            FROM asset_article a
            INNER JOIN asset_care_list al ON al.ARTICLE_ID = a.ARTICLE_ID
            WHERE al.ARTICLE_ID = ? 
            GROUP BY a.ARTICLE_NUM, al.CARE_LIST_ID
            ORDER BY al.CARE_LIST_NAME
        ', [$article_id]);

        return response()->json($data);
    }

    public static function calculateAge($receiveDate)
    {
        if (!$receiveDate) {
            return '-';
        }
        try {
            $birth = new \DateTime($receiveDate);
            $now = new \DateTime();
            $diff = $birth->diff($now);
            
            $parts = [];
            if ($diff->y > 0) {
                $parts[] = $diff->y . " ปี";
            }
            if ($diff->m > 0) {
                $parts[] = $diff->m . " เดือน";
            }
            if ($diff->d > 0) {
                $parts[] = $diff->d . " วัน";
            }
            
            return empty($parts) ? "0 วัน" : implode(" ", $parts);
        } catch (\Exception $e) {
            return '-';
        }
    }

    public static function formatThaiDate($date, $format = 'full')
    {
        if (!$date) return '-';
        try {
            $carbon = Carbon::parse($date);
            $thaiMonthsShort = [
                '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
                '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
                '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
            ];
            $thaiMonthsFull = [
                '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
                '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
                '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
            ];
            
            $year = $carbon->year + 543;
            $month = $carbon->format('m');
            $day = $carbon->day;
            
            if ($format == 'short') {
                return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $year;
            }
            
            return $day . ' ' . $thaiMonthsFull[$month] . ' พ.ศ. ' . $year;
        } catch (\Exception $e) {
            return '-';
        }
    }
}
