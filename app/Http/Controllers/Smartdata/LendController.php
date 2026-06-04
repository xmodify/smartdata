<?php

namespace App\Http\Controllers\Smartdata;

use App\Http\Controllers\Controller;
use App\Models\LendItem;
use App\Models\LendTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LendController extends Controller
{
    /**
     * Dashboard — รายการยืมทั้งหมด + สถิติ
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $status_filter = $request->status ?? 'all';
        $search        = $request->search ?? '';

        $query = LendTransaction::with(['lendItem', 'creator'])
            ->orderByDesc('borrow_date')
            ->orderByDesc('created_at');

        // Filter สถานะ
        if ($status_filter === 'overdue') {
            $query->where('status', 'borrowed')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now()->toDateString());
        } elseif ($status_filter !== 'all') {
            $query->where('status', $status_filter);
        }

        $transactions = $query->get();

        // สถิติสรุป
        $stats = [
            'borrowed'  => LendTransaction::where('status', 'borrowed')->count(),
            'overdue'   => LendTransaction::where('status', 'borrowed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->count(),
            'returned'  => LendTransaction::where('status', 'returned')->count(),
            'cancelled' => LendTransaction::where('status', 'cancelled')->count(),
        ];

        $lendItems = LendItem::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('smartdata.lend.index', compact('transactions', 'stats', 'status_filter', 'search', 'lendItems'));
    }

    /**
     * ฟอร์มยืมอุปกรณ์ใหม่
     */
    public function create()
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $lendItems = LendItem::active()->orderBy('sort_order')->orderBy('name')->get();
        return view('smartdata.lend.create', compact('lendItems'));
    }

    /**
     * บันทึกการยืม
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $request->validate([
            'borrower_name'    => 'required|string|max:255',
            'borrower_address' => 'nullable|string',
            'borrower_phone'   => 'nullable|string|max:20',
            'patient_name'     => 'nullable|string|max:255',
            'hn'               => 'nullable|string|max:20',
            'patient_address'  => 'nullable|string',
            'patient_phone'    => 'nullable|string|max:20',
            'lend_item_id'     => 'required|exists:lend_items,id',
            'qty'              => 'required|integer|min:1',
            'borrow_date'      => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:borrow_date',
            'deposit_amount'   => 'nullable|numeric|min:0',
            'deposit_receipt_no' => 'nullable|string|max:50',
            'note'             => 'nullable|string',
        ]);

        LendTransaction::create([
            'lend_item_id'       => $request->lend_item_id,
            'hn'                 => $request->hn,
            'borrower_name'      => $request->borrower_name,
            'borrower_address'   => $request->borrower_address,
            'borrower_phone'     => $request->borrower_phone,
            'patient_name'       => $request->patient_name,
            'patient_address'    => $request->patient_address,
            'patient_phone'      => $request->patient_phone,
            'borrow_date'        => $request->borrow_date,
            'due_date'           => $request->due_date,
            'qty'                => $request->qty,
            'deposit_amount'     => $request->deposit_amount,
            'deposit_receipt_no' => $request->deposit_receipt_no,
            'note'               => $request->note,
            'status'             => 'borrowed',
            'created_by'         => auth()->id(),
        ]);

        return redirect()->route('lend.index')->with('success', 'บันทึกการยืมอุปกรณ์เรียบร้อยแล้ว');
    }


    /**
     * อัพเดตรายการยืม
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $transaction = LendTransaction::findOrFail($id);

        if ($transaction->status === 'cancelled') {
            return back()->with('error', 'ไม่สามารถแก้ไขรายการที่ยกเลิกแล้วได้');
        }

        $request->validate([
            'borrower_name'    => 'required|string|max:255',
            'borrower_address' => 'nullable|string',
            'borrower_phone'   => 'nullable|string|max:20',
            'patient_name'     => 'nullable|string|max:255',
            'hn'               => 'nullable|string|max:20',
            'patient_address'  => 'nullable|string',
            'patient_phone'    => 'nullable|string|max:20',
            'lend_item_id'     => 'required|exists:lend_items,id',
            'qty'              => 'required|integer|min:1',
            'borrow_date'      => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:borrow_date',
            'deposit_amount'   => 'nullable|numeric|min:0',
            'deposit_receipt_no' => 'nullable|string|max:50',
            'note'             => 'nullable|string',
        ]);

        $transaction->update([
            'lend_item_id'       => $request->lend_item_id,
            'hn'                 => $request->hn,
            'borrower_name'      => $request->borrower_name,
            'borrower_address'   => $request->borrower_address,
            'borrower_phone'     => $request->borrower_phone,
            'patient_name'       => $request->patient_name,
            'patient_address'    => $request->patient_address,
            'patient_phone'      => $request->patient_phone,
            'borrow_date'        => $request->borrow_date,
            'due_date'           => $request->due_date,
            'qty'                => $request->qty,
            'deposit_amount'     => $request->deposit_amount,
            'deposit_receipt_no' => $request->deposit_receipt_no,
            'note'               => $request->note,
        ]);

        return redirect()->route('lend.index')->with('success', 'อัพเดตรายการเรียบร้อยแล้ว');
    }

    /**
     * บันทึกการคืน (ผ่าน edit form)
     */
    public function processReturn(Request $request, $id)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $transaction = LendTransaction::findOrFail($id);

        if ($transaction->status !== 'borrowed') {
            return back()->with('error', 'รายการนี้ไม่อยู่ในสถานะกำลังยืม');
        }

        $request->validate([
            'return_date'       => 'required|date',
            'return_time'       => 'nullable|date_format:H:i',
            'returner_name'     => 'nullable|string|max:255',
            'returner_address'  => 'nullable|string',
            'returner_phone'    => 'nullable|string|max:20',
            'returned_note'     => 'nullable|string',
        ]);

        $transaction->update([
            'status'            => 'returned',
            'return_date'       => $request->return_date,
            'return_time'       => $request->return_time ?? now()->format('H:i:s'),
            'returner_name'     => $request->returner_name,
            'returner_address'  => $request->returner_address,
            'returner_phone'    => $request->returner_phone,
            'returned_by'       => auth()->id(),
            'returned_note'     => $request->returned_note,
        ]);

        return redirect()->route('lend.index')->with('success', 'บันทึกการคืนอุปกรณ์เรียบร้อยแล้ว');
    }

    /**
     * ยกเลิกรายการ (แทนการลบ)
     */
    public function cancel(Request $request, $id)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $transaction = LendTransaction::findOrFail($id);

        if ($transaction->status === 'cancelled') {
            return back()->with('error', 'รายการนี้ถูกยกเลิกแล้ว');
        }

        $request->validate([
            'cancelled_reason' => 'required|string|max:500',
        ]);

        $transaction->update([
            'status'            => 'cancelled',
            'cancelled_by'      => auth()->id(),
            'cancelled_at'      => now(),
            'cancelled_reason'  => $request->cancelled_reason,
        ]);

        return redirect()->route('lend.index')->with('success', 'ยกเลิกรายการเรียบร้อยแล้ว');
    }

    /**
     * พิมพ์ใบยืมอุปกรณ์
     */
    public function printForm($id)
    {
        if (!auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $transaction = LendTransaction::with(['lendItem', 'creator'])->findOrFail($id);
        return view('smartdata.lend.print', compact('transaction'));
    }
}
