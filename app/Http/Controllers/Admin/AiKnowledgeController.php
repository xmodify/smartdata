<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiKnowledgeDoc;
use App\Models\AiKnowledgeChunk;
use App\Models\AiKnowledgeCategory;
use App\Services\Ai\VectorRagService;
use App\Services\Ai\AiManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class AiKnowledgeController extends Controller
{
    protected VectorRagService $ragService;

    public function __construct(VectorRagService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $docs = AiKnowledgeDoc::withCount('chunks')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = AiKnowledgeCategory::withCount('docs')
            ->orderBy('name', 'asc')
            ->get();

        $stats = [
            'total_docs' => $docs->count(),
            'indexed_docs' => $docs->where('status', 'indexed')->count(),
            'total_chunks' => AiKnowledgeChunk::count(),
            'total_categories' => $categories->count(),
            'active_provider' => AiManager::getActiveProvider()->getProviderName()
        ];

        return view('admin.ai.knowledge', compact('docs', 'stats', 'categories'));
    }

    public function upload(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'document' => 'required|file|max:51200|mimes:pdf,docx,txt,md',
        ], [
            'title.required' => 'กรุณากรอกชื่อเอกสาร',
            'category.required' => 'กรุณาระบุหมวดหมู่',
            'document.required' => 'กรุณาเลือกไฟล์เอกสาร',
            'document.max' => 'ขนาดไฟล์ต้องไม่เกิน 50 MB',
            'document.mimes' => 'รองรับเฉพาะไฟล์ PDF, DOCX, TXT, MD เท่านั้น',
        ]);

        try {
            $file = $request->file('document');
            $origName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $size = $file->getSize();

            // Store in storage/app/public/knowledge
            $storedName = Str::uuid() . '.' . $ext;
            $path = $file->storeAs('knowledge', $storedName, 'public');

            $doc = AiKnowledgeDoc::create([
                'title' => $request->input('title'),
                'category' => $request->input('category'),
                'filename' => $origName,
                'file_path' => $path,
                'file_size' => $size,
                'file_type' => $ext,
                'status' => 'unindexed',
                'uploaded_by' => auth()->id(),
            ]);

            // If user checked "Embed Immediately"
            if ($request->boolean('embed_immediately')) {
                $res = $this->ragService->indexDocument($doc);
                if (!$res['success']) {
                    return redirect()->route('admin.ai.knowledge')
                        ->with('warning', 'อัปโหลดเอกสารสำเร็จ แต่การแปลง Vector ล้มเหลว: ' . $res['message']);
                }
                return redirect()->route('admin.ai.knowledge')
                    ->with('success', "อัปโหลดและแปลง Vector เอกสาร \"{$doc->title}\" สำเร็จเรียบร้อย ({$res['chunks_count']} Chunks)");
            }

            return redirect()->route('admin.ai.knowledge')
                ->with('success', "อัปโหลดเอกสาร \"{$doc->title}\" เข้าสู่คลังเรียบร้อย (ยังไม่ได้แปลง Vector)");
        } catch (Exception $e) {
            return redirect()->route('admin.ai.knowledge')
                ->with('error', 'เกิดข้อผิดพลาดในการอัปโหลด: ' . $e->getMessage());
        }
    }

    public function embed(int $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $doc = AiKnowledgeDoc::findOrFail($id);
        $res = $this->ragService->indexDocument($doc);

        if (request()->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->route('admin.ai.knowledge')
                ->with('success', "แปลง Vector สำหรับ \"{$doc->title}\" สำเร็จ! ({$res['chunks_count']} Chunks)");
        }

        return redirect()->route('admin.ai.knowledge')
            ->with('error', "แปลง Vector ล้มเหลว: " . $res['message']);
    }

    public function reEmbedAll()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->ragService->reEmbedAll();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Re-Embed เอกสารทั้งหมดเรียบร้อย (สำเร็จ {$result['success_count']} / {$result['total']} เล่ม)",
                'details' => $result
            ]);
        }

        return redirect()->route('admin.ai.knowledge')
            ->with('success', "Re-Embed สำเร็จ {$result['success_count']} จากทั้งหมด {$result['total']} เอกสาร");
    }

    public function destroy(int $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $doc = AiKnowledgeDoc::findOrFail($id);
        $title = $doc->title;
        $doc->deleteWithFiles();

        return redirect()->route('admin.ai.knowledge')
            ->with('success', "ลบเอกสาร \"{$title}\" พร้อมไฟล์และ Vector เรียบร้อยแล้ว (Cascade Delete สะอาด 100%)");
    }

    public function testSearch(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = $request->input('query', '');
        if (empty(trim($query))) {
            return response()->json(['success' => false, 'message' => 'กรุณากรอกข้อความค้นหา']);
        }

        $result = $this->ragService->searchAndAnswer($query);
        return response()->json($result);
    }

    /**
     * Store a new document category.
     */
    public function storeCategory(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:191|unique:ai_knowledge_categories,name',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique' => 'มีหมวดหมู่นี้ในระบบแล้ว',
            'name.max' => 'ชื่อหมวดหมู่ต้องไม่เกิน 191 ตัวอักษร',
        ]);

        $category = AiKnowledgeCategory::create([
            'name' => trim($request->input('name')),
            'description' => trim($request->input('description', '')),
            'color' => $request->input('color', '#10b981') ?: '#10b981',
            'icon' => $request->input('icon', 'fa-folder') ?: 'fa-folder',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "เพิ่มหมวดหมู่ \"{$category->name}\" สำเร็จ",
                'category' => $category
            ]);
        }

        return redirect()->route('admin.ai.knowledge')
            ->with('success', "เพิ่มหมวดหมู่ \"{$category->name}\" สำเร็จเรียบร้อย");
    }

    /**
     * Update an existing document category.
     */
    public function updateCategory(Request $request, int $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $category = AiKnowledgeCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:191|unique:ai_knowledge_categories,name,' . $id,
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique' => 'มีชื่อหมวดหมู่นี้ในระบบแล้ว',
            'name.max' => 'ชื่อหมวดหมู่ต้องไม่เกิน 191 ตัวอักษร',
        ]);

        $oldName = $category->name;
        $newName = trim($request->input('name'));

        $category->update([
            'name' => $newName,
            'description' => trim($request->input('description', '')),
            'color' => $request->input('color', '#10b981') ?: '#10b981',
            'icon' => $request->input('icon', 'fa-folder') ?: 'fa-folder',
        ]);

        // If category name changed, update all existing documents with this category
        if ($oldName !== $newName) {
            AiKnowledgeDoc::where('category', $oldName)->update(['category' => $newName]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "แก้ไขหมวดหมู่ \"{$category->name}\" สำเร็จ",
                'category' => $category
            ]);
        }

        return redirect()->route('admin.ai.knowledge')
            ->with('success', "แก้ไขหมวดหมู่ \"{$category->name}\" สำเร็จเรียบร้อย");
    }

    /**
     * Delete a document category.
     */
    public function destroyCategory(int $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $category = AiKnowledgeCategory::findOrFail($id);
        $docsCount = AiKnowledgeDoc::where('category', $category->name)->count();

        if ($docsCount > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "ไม่สามารถลบหมวดหมู่ \"{$category->name}\" ได้ เนื่องจากมีเอกสารใช้งานอยู่ {$docsCount} เล่ม กรุณาย้ายหรือลบเอกสารก่อน"
                ], 422);
            }

            return redirect()->route('admin.ai.knowledge')
                ->with('error', "ไม่สามารถลบหมวดหมู่ \"{$category->name}\" ได้ เนื่องจากมีเอกสารใช้งานอยู่ {$docsCount} เล่ม กรุณาย้ายหรือลบเอกสารก่อน");
        }

        $catName = $category->name;
        $category->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "ลบหมวดหมู่ \"{$catName}\" เรียบร้อยแล้ว"
            ]);
        }

        return redirect()->route('admin.ai.knowledge')
            ->with('success', "ลบหมวดหมู่ \"{$catName}\" เรียบร้อยแล้ว");
    }
}
