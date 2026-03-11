<?php

namespace App\Http\Controllers\Smartdata;

use App\Http\Controllers\Controller;
use App\Models\Skpcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkpcardController extends Controller
{
    public function index(Request $request)
    {
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $cards = Skpcard::whereBetween('buy_date', [$start_date, $end_date])
            ->orderBy('buy_date', 'desc')
            ->get();

        // Calculate Monthly Stats for the Chart
        $statsRaw = Skpcard::selectRaw("
                MONTH(buy_date) as m,
                YEAR(buy_date) as y,
                SUM(CASE WHEN price = 1000 THEN 1 ELSE 0 END) as count_1000,
                SUM(CASE WHEN price = 1500 THEN 1 ELSE 0 END) as count_1500,
                SUM(CAST(price AS DECIMAL(10,2))) as total_income
            ")
            ->whereBetween('buy_date', [$start_date, $end_date])
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get();

        $monthNames = [
            10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
            4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.'
        ];

        $chartData = [
            'labels' => [],
            'count_1000' => [],
            'count_1500' => [],
            'total_income' => []
        ];

        // We want to show all 12 months in budget year order (Oct-Sep)
        $currentDate = Carbon::parse($start_date);
        for ($i = 0; $i < 12; $i++) {
            $m = (int)$currentDate->format('m');
            $y = (int)$currentDate->format('Y');
            
            $label = $monthNames[$m] . ' ' . ($y + 543);
            $chartData['labels'][] = $label;
            
            $match = $statsRaw->where('m', $m)->where('y', $y)->first();
            
            $chartData['count_1000'][] = $match ? (int)$match->count_1000 : 0;
            $chartData['count_1500'][] = $match ? (int)$match->count_1500 : 0;
            $chartData['total_income'][] = $match ? (float)$match->total_income : 0;
            
            $currentDate->addMonth();
        }

        return view('smartdata.skpcard.index', compact(
            'cards', 
            'budget_year', 
            'budget_year_select', 
            'start_date', 
            'end_date',
            'chartData'
        ));
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        if ($year_data) {
            $start_date = $year_data->DATE_BEGIN;
            $end_date = $year_data->DATE_END;
        } else {
            $start_date = ($budget_year - 543) . '-10-01';
            $end_date = ($budget_year - 542) . '-09-30';
        }

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'cid' => 'required|string|max:13',
            'name' => 'required|string',
            'buy_date' => 'required|date',
            'price' => 'required|string',
        ]);

        $buyDate = Carbon::parse($request->buy_date);
        $exDate = $buyDate->copy()->addYear();

        Skpcard::create([
            'cid' => $request->cid,
            'name' => $request->name,
            'birthday' => $request->birthday,
            'address' => $request->address,
            'phone' => $request->phone,
            'buy_date' => $request->buy_date,
            'ex_date' => $exDate->format('Y-m-d'),
            'price' => $request->price,
            'rcpt' => $request->rcpt,
        ]);

        return redirect()->back()->with('success', 'บันทึกข้อมูลการซื้อบัตรเรียบร้อยแล้ว');
    }

    public function update(Request $request, Skpcard $skpcard)
    {
        $request->validate([
            'cid' => 'required|string|max:13',
            'name' => 'required|string',
            'buy_date' => 'required|date',
            'price' => 'required|string',
        ]);

        $buyDate = Carbon::parse($request->buy_date);
        $exDate = $buyDate->copy()->addYear();

        $skpcard->update([
            'cid' => $request->cid,
            'name' => $request->name,
            'birthday' => $request->birthday,
            'address' => $request->address,
            'phone' => $request->phone,
            'buy_date' => $request->buy_date,
            'ex_date' => $exDate->format('Y-m-d'),
            'price' => $request->price,
            'rcpt' => $request->rcpt,
        ]);

        return redirect()->back()->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(Skpcard $skpcard)
    {
        $skpcard->delete();
        return redirect()->back()->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
