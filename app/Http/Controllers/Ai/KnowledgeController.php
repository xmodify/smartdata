<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiKnowledgeDoc;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KnowledgeController extends Controller
{
    /**
     * User-facing Digital Library view.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $query = AiKnowledgeDoc::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $docs = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        $categories = AiKnowledgeDoc::select('category')
            ->distinct()
            ->pluck('category');

        return view('ai.knowledge', compact('docs', 'categories', 'search', 'category'));
    }

    /**
     * View/Preview document in browser (PDF inline viewer).
     */
    public function view(int $id)
    {
        $doc = AiKnowledgeDoc::findOrFail($id);
        $path = $doc->storage_path;

        if (!file_exists($path)) {
            abort(404, 'ไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์');
        }

        $mimeType = match (strtolower($doc->file_type)) {
            'pdf' => 'application/pdf',
            'txt', 'md' => 'text/plain; charset=utf-8',
            default => 'application/octet-stream'
        };

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . rawurlencode($doc->filename) . '"'
        ]);
    }

    /**
     * Download original document directly.
     */
    public function download(int $id): BinaryFileResponse
    {
        $doc = AiKnowledgeDoc::findOrFail($id);
        $path = $doc->storage_path;

        if (!file_exists($path)) {
            abort(404, 'ไม่พบไฟล์เอกสารบนเซิร์ฟเวอร์');
        }

        return response()->download($path, $doc->filename);
    }
}
