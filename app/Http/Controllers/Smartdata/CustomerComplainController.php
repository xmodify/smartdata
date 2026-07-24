<?php

namespace App\Http\Controllers\Smartdata;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CustomerComplainController extends Controller
{
    /**
     * แสดงรายการเรื่องร้องเรียน/ข้อเสนอแนะทั้งหมด (ต้อง Login)
     */
    public function index()
    {
        $complains = CustomerComplain::latest()->paginate(20);
        return view('smartdata.customer_complain.index', compact('complains'));
    }

    /**
     * สร้าง QR Code สำหรับ URL ฟอร์มรับความคิดเห็น
     */
    public function qrcode(Request $request)
    {
        $url = route('customer_complain.create');

        $options = new QROptions([
            'outputType'  => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
            'eccLevel'    => \chillerlan\QRCode\QRCode::ECC_M,
            'scale'       => 8,
            'imageBase64' => false,
        ]);

        $qrcode = (new QRCode($options))->render($url);

        $headers = ['Content-Type' => 'image/png'];
        if ($request->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="qrcode-customer-complain.png"';
        }

        return response($qrcode, 200, $headers);
    }

    /**
     * แสดงฟอร์มรับเรื่องร้องเรียน/ข้อเสนอแนะ/คำชมเชย (สาธารณะ ไม่ต้อง Login)
     */
    public function create()
    {
        return view('smartdata.customer_complain.create');
    }

    /**
     * บันทึกข้อมูล + ส่ง MOPH Notify
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:คำชมเชย,ข้อเสนอแนะ,ข้อร้องเรียน,อื่น ๆ',
            'name'      => 'nullable|string|max:255',
            'detail'    => 'nullable|string',
            'call_back' => 'required|in:ต้องการ,ไม่ต้องการ',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:191',
        ]);

        CustomerComplain::create([
            'type'      => $request->type,
            'name'      => $request->name,
            'detail'    => $request->detail,
            'call_back' => $request->call_back,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'status'    => 'รอดำเนินการ',
        ]);

        // ─── ส่ง MOPH Notify ───────────────────────────────────────────────
        $this->sendMophNotify($request);
        // ──────────────────────────────────────────────────────────────────

        return redirect()->route('customer_complain.create')
            ->with('success', 'ขอบคุณสำหรับความคิดเห็นของท่าน ข้อมูลได้รับการบันทึกเรียบร้อยแล้ว');
    }

    /**
     * ส่งการแจ้งเตือนผ่าน MOPH Notify (ไม่ blocking — ล้มเหลวก็ไม่กระทบ user)
     */
    private function sendMophNotify(Request $request): void
    {
        try {
            $client = DB::table('moph_notify')
                ->where('id', 3)
                ->where('active', 'Y')
                ->first(['client_id', 'secret', 'name']);

            if (!$client) {
                return;
            }

            // ─── ข้อมูลจาก request ───
            $typeEmoji = match ($request->type) {
                'คำชมเชย'      => '👏',
                'ข้อเสนอแนะ'  => '💡',
                'ข้อร้องเรียน' => '⚠️',
                default        => '📝',
            };

            $name     = $request->name     ?: 'ไม่ระบุ';
            $detail   = $request->detail   ?: '-';
            $callBack = $request->call_back ?? 'ไม่ต้องการ';

            if (mb_strlen($detail) > 150) {
                $detail = mb_substr($detail, 0, 150) . '...';
            }

            // ─── วันที่ภาษาไทย ───
            $now = now();
            $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                               'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            $dateText = $now->day . ' ' . $thaiMonths[$now->month] . ' ' . ($now->year + 543)
                      . ' (' . $now->format('H:i') . ')';

            // ─── สร้างข้อความ (style เดียวกับ ReplicationController) ───
            $message  = "{$typeEmoji} {$request->type}: มีรายการใหม่\n";
            $message .= "-----------------------\n";
            $message .= "ชื่อ: {$name}\n";
            $message .= "รายละเอียด: {$detail}\n";
            $message .= "ติดต่อกลับ: {$callBack}";

            if ($callBack === 'ต้องการ') {
                $phone = $request->phone ?: '-';
                $email = $request->email ?: '-';
                $message .= "\nโทรศัพท์: {$phone}";
                if ($email !== '-') {
                    $message .= "\nEmail: {$email}";
                }
            }

            $message .= "\n-----------------------\n";
            $message .= "รับเรื่อง: {$dateText}";

            Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key'   => $client->client_id,
                    'secret-key'   => $client->secret,
                ])
                ->post('https://morpromt2f.moph.go.th/api/notify/send', [
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $message,
                        ],
                    ],
                ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('CustomerComplain MophNotify failed: ' . $e->getMessage());
        }
    }
}
