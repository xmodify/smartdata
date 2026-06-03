<?php

namespace App\Http\Controllers\Smartdata;

use App\Http\Controllers\Controller;
use App\Models\LendItem;
use Illuminate\Http\Request;

class LendItemController extends Controller
{
    /**
     * หน้าตั้งค่ารายการทั้งหมด
     */
    public function index()
    {
        if (!auth()->user()->hasAccessRole('admin') && !auth()->user()->hasAccessLend()) {
            abort(403);
        }

        $items = LendItem::orderBy('sort_order')->orderBy('name')->get();
        return view('smartdata.lend.settings', compact('items'));
    }

    /**
     * เพิ่มรายการใหม่
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAccessRole('admin')) {
            abort(403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:equipment,medicine',
            'description' => 'nullable|string',
            'total_qty'   => 'required|integer|min:1',
            'sort_order'  => 'nullable|integer',
        ]);

        LendItem::create([
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
            'total_qty'   => $request->total_qty,
            'active'      => 'Y',
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'เพิ่มรายการเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขรายการ
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasAccessRole('admin')) {
            abort(403);
        }

        $item = LendItem::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:equipment,medicine',
            'description' => 'nullable|string',
            'total_qty'   => 'required|integer|min:1',
            'sort_order'  => 'nullable|integer',
            'active'      => 'nullable|in:Y,N',
        ]);

        $item->update([
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
            'total_qty'   => $request->total_qty,
            'active'      => $request->active ?? 'Y',
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'แก้ไขรายการเรียบร้อยแล้ว');
    }

    /**
     * Toggle active (ไม่มีลบ)
     */
    public function toggleActive($id)
    {
        if (!auth()->user()->hasAccessRole('admin')) {
            abort(403);
        }

        $item = LendItem::findOrFail($id);
        $item->update(['active' => $item->active === 'Y' ? 'N' : 'Y']);

        $statusText = $item->active === 'Y' ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        return back()->with('success', "{$item->name} $statusText เรียบร้อยแล้ว");
    }
}
