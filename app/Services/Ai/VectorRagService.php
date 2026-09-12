<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Models\AiKnowledgeDoc;
use App\Models\AiKnowledgeChunk;
use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Storage;
use Exception;
use ZipArchive;
use Log;

class VectorRagService
{
    protected LlmProviderInterface $provider;

    public function __construct(?LlmProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiManager::getActiveProvider();
    }

    /**
     * Process an uploaded document: extract text, chunk, and embed.
     */
    public function indexDocument(AiKnowledgeDoc $doc): array
    {
        $doc->update(['status' => 'indexing', 'error_message' => null]);

        try {
            $filePath = $doc->storage_path;
            if (!file_exists($filePath)) {
                throw new Exception("ไม่พบไฟล์เอกสารที่พาธ: {$filePath}");
            }

            // 1. Extract text
            $text = $this->extractText($filePath, $doc->file_type);
            if (empty(trim($text))) {
                throw new Exception("ไม่สามารถดึงข้อความจากเอกสารได้ หรือเอกสารเป็นหน้าว่าง/ภาพสแกน");
            }

            // 2. Chunk text
            $chunks = $this->chunkText($text, 600, 100);
            if (empty($chunks)) {
                throw new Exception("ไม่สามารถแบ่งส่วนข้อความ (Chunking) ได้");
            }

            // 3. Clear old chunks if re-indexing
            AiKnowledgeChunk::where('doc_id', $doc->id)->delete();

            // 4. Generate embeddings and save chunks
            $savedCount = 0;
            $sampleDim = null;

            foreach ($chunks as $idx => $chunkContent) {
                // Call embedding API
                $vector = $this->provider->embed($chunkContent);
                $dim = count($vector);
                if ($sampleDim === null) {
                    $sampleDim = $dim;
                }

                AiKnowledgeChunk::create([
                    'doc_id' => $doc->id,
                    'chunk_index' => $idx + 1,
                    'content' => $chunkContent,
                    'embedding' => $vector,
                    'embedding_dim' => $dim,
                    'page_number' => null,
                ]);

                $savedCount++;
            }

            $doc->update([
                'status' => 'indexed',
                'chunks_count' => $savedCount,
                'embedded_model' => $this->provider->getProviderName(),
                'error_message' => null,
            ]);

            return [
                'success' => true,
                'message' => "สร้าง Vector สำเร็จ ({$savedCount} Chunks, {$sampleDim} มิติ)",
                'chunks_count' => $savedCount,
                'dimensions' => $sampleDim
            ];
        } catch (Exception $e) {
            $doc->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => "การแปลง Vector ล้มเหลว: " . $e->getMessage()
            ];
        }
    }

    /**
     * Search knowledge base using Vector Cosine Similarity and generate an answer.
     */
    public function searchAndAnswer(string $question, ?int $specificDocId = null): array
    {
        $topK = (int) AiSetting::get('rag_top_k', 4);
        $minScore = (float) AiSetting::get('rag_min_score', 0.55);

        // 1. Generate embedding for user query
        try {
            $queryVector = $this->provider->embed($question);
        } catch (Exception $e) {
            return [
                'success' => false,
                'answer' => "ไม่สามารถเชื่อมต่อระบบสร้าง Embedding เพื่อสืบค้นเอกสารได้: " . $e->getMessage(),
                'sources' => []
            ];
        }

        // 2. Fetch candidate chunks from DB
        $query = AiKnowledgeChunk::with('document')
            ->whereNotNull('embedding');

        if ($specificDocId) {
            $query->where('doc_id', $specificDocId);
        }

        $allChunks = $query->get();
        if ($allChunks->isEmpty()) {
            return [
                'success' => false,
                'answer' => "ยังไม่มีเอกสารใดในคลังความรู้ที่ถูกแปลงเป็น Vector หรือยังไม่มีเอกสารที่เกี่ยวข้อง",
                'sources' => []
            ];
        }

        // 3. Compute Cosine Similarity for each chunk
        $ranked = [];
        foreach ($allChunks as $chunk) {
            $chunkVector = $chunk->embedding;
            if (!is_array($chunkVector) || empty($chunkVector)) {
                continue;
            }

            // Dimension match check
            if (count($chunkVector) !== count($queryVector)) {
                continue; // Skips chunks indexed with different models
            }

            $score = $this->cosineSimilarity($queryVector, $chunkVector);
            if ($score >= $minScore) {
                $ranked[] = [
                    'chunk' => $chunk,
                    'score' => round($score, 4)
                ];
            }
        }

        // Sort by score DESC
        usort($ranked, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Take Top K
        $topMatches = array_slice($ranked, 0, $topK);

        if (empty($topMatches)) {
            return [
                'success' => true,
                'answer' => "ขออภัย จากการสืบค้นคลังความรู้โรงพยาบาล ไม่พบบทความหรือระเบียบปฏิบัติที่เกี่ยวข้องโดยตรงกับคำถามนี้ (คะแนนความสอดคล้องต่ำกว่าเกณฑ์ {$minScore})",
                'sources' => []
            ];
        }

        // 4. Construct context for LLM synthesis
        $contextText = "";
        $sources = [];
        foreach ($topMatches as $i => $match) {
            $chunk = $match['chunk'];
            $doc = $chunk->document;
            $docTitle = $doc ? $doc->title : 'เอกสารทั่วไป';
            $num = $i + 1;

            $contextText .= "\n--- [แหล่งอ้างอิงที่ {$num}: {$docTitle} (ชิ้นส่วน #{$chunk->chunk_index})] ---\n";
            $contextText .= $chunk->content . "\n";

            $sources[] = [
                'doc_id' => $chunk->doc_id,
                'title' => $docTitle,
                'chunk_index' => $chunk->chunk_index,
                'page_number' => $chunk->page_number,
                'score' => $match['score'],
                'snippet' => mb_substr($chunk->content, 0, 150) . '...'
            ];
        }

        $systemPrompt = "คุณคือ ดองกี้ ผู้ช่วยตอบคำถามจากคลังความรู้โรงพยาบาล
หน้าที่ของคุณคือ: ตอบคำถามของผู้ใช้โดยอ้างอิงข้อมูลจากบริบทเอกสารที่จัดเตรียมให้เท่านั้น โดยแทนตัวเองว่า 'ดองกี้' หรือ 'ผม' (ห้ามใส่สร้อยหรือวงเล็บต่อท้ายคำว่าดองกี้)
กฎเกณฑ์การตอบ:
1. ตอบเป็นภาษาไทยที่สุภาพ เข้าใจง่าย ถูกต้องตามหลักการทางการแพทย์และการบริหารงานโรงพยาบาล
2. อ้างอิงชื่อเอกสารที่ใช้ตอบ เช่น [อ้างอิงจาก: ชื่อนิติกรรม/คู่มือ CPG]
3. หากข้อมูลในบริบทเอกสารไม่มีคำตอบสำหรับคำถามนี้ ให้ระบุตรงๆ ว่าไม่พบข้อมูลในเอกสารที่สืบค้นได้ ห้ามแต่งเติมข้อมูลที่ไม่มีอยู่จริง";

        $userPrompt = "บริบทเอกสารอ้างอิงจากคลังความรู้:\n{$contextText}\n\nคำถาม: {$question}";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        try {
            $answer = $this->provider->chat($messages, ['temperature' => 0.2]);
            return [
                'success' => true,
                'answer' => $answer,
                'sources' => $sources
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'answer' => "สืบค้นเอกสารพบ แต่เกิดข้อผิดพลาดในการสร้างคำตอบจากโมเดล: " . $e->getMessage(),
                'sources' => $sources
            ];
        }
    }

    /**
     * Re-embed all indexed documents using current active provider.
     */
    public function reEmbedAll(): array
    {
        $docs = AiKnowledgeDoc::all();
        $success = 0;
        $failed = 0;
        $logs = [];

        foreach ($docs as $doc) {
            $res = $this->indexDocument($doc);
            if ($res['success']) {
                $success++;
                $logs[] = "{$doc->title}: สำเร็จ ({$res['chunks_count']} Chunks)";
            } else {
                $failed++;
                $logs[] = "{$doc->title}: ล้มเหลว ({$res['message']})";
            }
        }

        return [
            'total' => count($docs),
            'success_count' => $success,
            'failed_count' => $failed,
            'logs' => $logs
        ];
    }

    /**
     * Compute Cosine Similarity between two float vectors.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $len = min(count($a), count($b));
        if ($len === 0) return 0.0;

        for ($i = 0; $i < $len; $i++) {
            $valA = (float) $a[$i];
            $valB = (float) $b[$i];
            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        if ($normA <= 0.0 || $normB <= 0.0) return 0.0;

        return (float) ($dotProduct / (sqrt($normA) * sqrt($normB)));
    }

    /**
     * Chunk text with sliding window overlap.
     */
    public function chunkText(string $text, int $chunkSize = 600, int $overlap = 100): array
    {
        // Normalize whitespace and newlines
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = trim($text);

        $len = mb_strlen($text);
        if ($len <= $chunkSize) {
            return [$text];
        }

        $chunks = [];
        $start = 0;

        while ($start < $len) {
            $end = min($start + $chunkSize, $len);
            
            // Try to break at paragraph or newline or space in the latter half
            if ($end < $len) {
                $slice = mb_substr($text, $start, $chunkSize);
                $breakPos = false;
                foreach (["\n\n", "\n", ". ", " "] as $delimiter) {
                    $pos = mb_strrpos($slice, $delimiter);
                    if ($pos !== false && $pos > ($chunkSize * 0.5)) {
                        $breakPos = $pos + mb_strlen($delimiter);
                        break;
                    }
                }

                if ($breakPos !== false && $breakPos > $overlap) {
                    $end = $start + $breakPos;
                }
            }

            $chunk = trim(mb_substr($text, $start, $end - $start));
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }

            if ($end >= $len) {
                break;
            }

            // Strictly advance start position
            $nextStart = $end - $overlap;
            if ($nextStart <= $start) {
                $nextStart = $start + max(1, (int)($chunkSize * 0.5));
            }
            $start = $nextStart;
        }

        return $chunks;
    }

    /**
     * Extract raw text from file based on extension.
     */
    public function extractText(string $filePath, string $extension): string
    {
        $ext = strtolower($extension);

        if (in_array($ext, ['txt', 'md', 'csv', 'json'])) {
            return file_get_contents($filePath);
        }

        if ($ext === 'docx') {
            return $this->extractDocxText($filePath);
        }

        if ($ext === 'pdf') {
            return $this->extractPdfText($filePath);
        }

        return file_get_contents($filePath);
    }

    /**
     * Extract text from DOCX using ZipArchive XML parsing.
     */
    protected function extractDocxText(string $filePath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlData = $zip->getFromIndex($index);
                $zip->close();

                // Strip XML tags and retain spaces/linebreaks
                $xml = simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                if ($xml !== false) {
                    return strip_tags($xml->asXML());
                }
                return strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xmlData));
            }
            $zip->close();
        }
        return '';
    }

    /**
     * Extract text from PDF.
     */
    protected function extractPdfText(string $filePath): string
    {
        // Check if Smalot\PdfParser exists
        if (class_exists('Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            } catch (Exception $e) {
                Log::warning("Smalot PdfParser failed: " . $e->getMessage());
            }
        }

        // Native PDF text stream extraction fallback
        $content = @file_get_contents($filePath);
        if (!$content) return '';

        $text = '';
        // Look for stream contents
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/is', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                // Try decompressing flate stream
                $decompressed = @gzuncompress($stream);
                if ($decompressed === false) {
                    $decompressed = @gzinflate($stream);
                }
                $data = $decompressed ?: $stream;

                // Extract text within BT...ET blocks
                if (preg_match_all('/BT[\r\n]+(.*?)[\r\n]+ET/is', $data, $textBlocks)) {
                    foreach ($textBlocks[1] as $block) {
                        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $block, $tjMatches)) {
                            $text .= implode(' ', $tjMatches[1]) . "\n";
                        }
                    }
                }
            }
        }

        return trim($text) ?: strip_tags($content);
    }
}
