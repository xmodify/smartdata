<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MophAlert;
use App\Models\MophAlertDetail;
use App\Services\MophAlertService;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::validate(array_merge($credentials, ['active' => 'Y']))) {
            $user = User::where('username', $request->username)->first();

            // Check if 2FA is enabled globally
            $mophAlert = MophAlert::where('active', 'Y')->first();
            $is2faEnabled = $mophAlert && $mophAlert->enable_2fa === 'Y';

            if ($is2faEnabled) {
                // Generate OTP
                $otp = rand(100000, 999999);
                
                // Store in session
                session([
                    '2fa_pending_user_id' => $user->id,
                    '2fa_otp' => $otp,
                    '2fa_expires_at' => now()->addMinutes(5)
                ]);

                // Send OTP via Moph Alert to user's CID (username)
                $this->sendMophAlertOTP($user->username, $otp);

                // Redirect to 2FA page
                return redirect()->route('login.verify_2fa');
            } else {
                // No 2FA -> Log in immediately
                if (Auth::attempt(array_merge($credentials, ['active' => 'Y']), $request->boolean('remember'))) {
                    $request->session()->regenerate();
                    return redirect()->intended('/dashboard');
                }
            }
        }

        // Check if user exists but is inactive
        $user = User::where('username', $request->username)->first();
        if ($user && $user->active !== 'Y') {
            return back()->withErrors([
                'username' => 'บัญชีของคุณยังไม่ได้รับการอนุมัติการใช้งาน กรุณารอผู้ดูแลระบบอนุมัติ',
            ])->onlyInput('username');
        }

        return back()->withErrors([
            'username' => 'Username หรือ รหัสผ่าน ไม่ถูกต้อง',
        ])->onlyInput('username');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'username' => 'required'
        ]);

        $user = User::where('username', $request->username)->where('active', 'Y')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบรายชื่อผู้ใช้งานนี้ในระบบ หรือบัญชียังไม่ได้รับการอนุมัติ'
            ], 404);
        }

        // Check 2FA global status
        $mophAlert = MophAlert::where('active', 'Y')->first();
        $is2faEnabled = $mophAlert && $mophAlert->enable_2fa === 'Y';

        // Generate OTP
        $otp = rand(100000, 999999);

        if ($is2faEnabled) {
            // Store in 2FA pending session
            session([
                '2fa_pending_user_id' => $user->id,
                '2fa_otp' => $otp,
                '2fa_expires_at' => now()->addMinutes(5)
            ]);

            $this->sendMophAlertOTP($user->username, $otp);

            return response()->json([
                'success' => true,
                'redirect' => route('login.verify_2fa'),
                'message' => 'ระบบเปิดใช้งาน 2FA กำลังนำทางไปหน้ากรอก OTP...'
            ]);
        } else {
            // Store in passwordless OTP session
            session([
                'otp_pending_user_id' => $user->id,
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(5)
            ]);

            $this->sendMophAlertOTP($user->username, $otp);

            return response()->json([
                'success' => true,
                'message' => 'รหัส OTP ถูกส่งไปยัง หมอพร้อม LineOA ของท่านแล้ว'
            ]);
        }
    }

    public function verifyOtpPasswordless(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'otp' => 'required|numeric'
        ]);

        $pendingUserId = session('otp_pending_user_id');
        $sessionOtp = session('otp_code');
        $expiresAt = session('otp_expires_at');

        if (!$pendingUserId || !$sessionOtp || now()->gt($expiresAt)) {
            return response()->json([
                'success' => false,
                'message' => 'รหัส OTP หมดอายุแล้ว กรุณากดขอรหัส OTP ใหม่อีกครั้ง'
            ], 400);
        }

        $user = User::find($pendingUserId);
        if (!$user || $user->username !== $request->username) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลผู้ใช้ไม่ตรงกับรหัส OTP'
            ], 400);
        }

        if (strval($request->otp) === strval($sessionOtp)) {
            // Clear pending session
            session()->forget(['otp_pending_user_id', 'otp_code', 'otp_expires_at']);
            
            // Login
            Auth::login($user);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'redirect' => url('/dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'รหัส OTP ไม่ถูกต้อง'
        ], 400);
    }

    public function show2faForm()
    {
        if (!session('2fa_pending_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.verify-2fa');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $pendingUserId = session('2fa_pending_user_id');
        $sessionOtp = session('2fa_otp');
        $expiresAt = session('2fa_expires_at');

        if (!$pendingUserId || !$sessionOtp || now()->gt($expiresAt)) {
            return redirect()->route('login')->withErrors(['otp' => 'รหัส OTP หมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่อีกครั้ง']);
        }

        if (strval($request->otp) === strval($sessionOtp)) {
            // Success! Clear session
            session()->forget(['2fa_pending_user_id', '2fa_otp', '2fa_expires_at']);

            $user = User::find($pendingUserId);
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['otp' => 'รหัส OTP ไม่ถูกต้อง']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function sendMophAlertOTP($cid, $otp)
    {
        $title = "รหัสผ่านชั่วคราว OTP";
        $messageText = "รหัส OTP ของคุณคือ {$otp} (ใช้งานได้ภายใน 5 นาที)";
        
        $messageHtml = '
        <div style="font-family: \'Sarabun\', sans-serif; padding: 16px; background-color: #ffffff; border-left: 5px solid #dc3545; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin: 8px 0; border-top: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;">
            <div style="font-size: 16px; font-weight: bold; color: #dc3545; margin-bottom: 8px; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px;">🔐 รหัสผ่านชั่วคราว (OTP)</div>
            <div style="font-size: 20px; font-weight: bold; color: #333333; letter-spacing: 4px; text-align: center; margin: 15px 0;">' . $otp . '</div>
            <div style="font-size: 13px; color: #666666; line-height: 1.6; text-align: center;">รหัสนี้มีอายุการใช้งาน 5 นาที เพื่อความปลอดภัยกรุณาอย่าเผยแพร่รหัสนี้แก่บุคคลอื่น</div>
            <div style="margin-top: 16px; font-size: 11px; color: #888888; text-align: right; border-top: 1px dashed #eeeeee; padding-top: 8px;">ระบบความปลอดภัย SmartData</div>
        </div>';

        MophAlertService::sendFreeForm($cid, $title, $messageText, $messageHtml, 1);
        
        // Also save log to moph_alert_detail
        try {
            MophAlertDetail::create([
                'moph_alert_id' => 1,
                'user_id' => null,
                'title' => $title,
                'message_text' => $messageText,
                'message_html' => $messageHtml,
                'recipient_count' => 1,
                'recipients' => [$cid],
                'status' => 'success',
                'response_message' => 'Sent OTP successfully',
            ]);
        } catch (\Exception $e) {
            // Ignore logging error
        }
    }

    public function redirectToProviderId()
    {
        $config = \App\Models\ProviderId::where('active', 'Y')->first();
        if (!$config || !$config->health_id_client_id || !$config->health_id_secret) {
            return redirect()->route('login')->withErrors(['provider_id' => 'ระบบล็อคอินด้วย Provider ID ยังไม่เปิดใช้งาน หรือยังไม่ได้ตั้งค่าเชื่อมต่อ']);
        }

        $isUat = (strpos(strtolower($config->health_id_client_id), 'uat') !== false || strpos(strtolower($config->health_id_client_id), 'test') !== false);
        $healthIdUrl = $isUat ? 'https://uat-moph.id.th' : 'https://moph.id.th';

        // Construct redirect URI using scheme and host from APP_URL config
        $appUrl = config('app.url');
        $parsedApp = parse_url($appUrl);
        $scheme = $parsedApp['scheme'] ?? 'http';
        $host = $parsedApp['host'] ?? 'localhost';
        $port = isset($parsedApp['port']) ? ':' . $parsedApp['port'] : '';
        $path = parse_url(route('login.provider_id.callback'), PHP_URL_PATH);
        $redirectUri = "{$scheme}://{$host}{$port}{$path}";

        $url = "{$healthIdUrl}/oauth/redirect?" . http_build_query([
            'client_id' => $config->health_id_client_id,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code'
        ]);

        return redirect()->away($url);
    }

    public function handleProviderIdCallback(Request $request)
    {
        try {
            $code = $request->query('code');
            if (!$code) {
                return redirect()->route('login')->withErrors(['provider_id' => 'ไม่ได้รับรหัสความถูกต้อง (Authorization Code) จากระบบ Health ID']);
            }

            $config = \App\Models\ProviderId::where('active', 'Y')->first();
            if (!$config) {
                return redirect()->route('login')->withErrors(['provider_id' => 'ระบบตั้งค่า Provider ID ถูกปิดการใช้งาน']);
            }

            $isUat = (strpos(strtolower($config->health_id_client_id), 'uat') !== false || strpos(strtolower($config->health_id_client_id), 'test') !== false);
            $healthIdUrl = $isUat ? 'https://uat-moph.id.th' : 'https://moph.id.th';
            $providerIdUrl = $isUat ? 'https://uat-provider.id.th' : 'https://provider.id.th';

            // Construct redirect URI using scheme and host from APP_URL config
            $appUrl = config('app.url');
            $parsedApp = parse_url($appUrl);
            $scheme = $parsedApp['scheme'] ?? 'http';
            $host = $parsedApp['host'] ?? 'localhost';
            $port = isset($parsedApp['port']) ? ':' . $parsedApp['port'] : '';
            $path = parse_url(route('login.provider_id.callback'), PHP_URL_PATH);
            $redirectUri = "{$scheme}://{$host}{$port}{$path}";

            // 1. Get Health ID Access Token
            $responseHealthToken = \Illuminate\Support\Facades\Http::timeout(15)
                ->withoutVerifying()
                ->asForm()
                ->post("{$healthIdUrl}/api/v1/token", [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'client_id' => $config->health_id_client_id,
                    'client_secret' => $config->health_id_secret,
                ]);

            if (!$responseHealthToken->successful()) {
                Log::error('Health ID Token Exchange failed: ' . $responseHealthToken->body());
                return redirect()->route('login')->withErrors(['provider_id' => 'การยืนยันรหัสกับ Health ID ล้มเหลว: ' . ($responseHealthToken->json('message') ?: 'การติดต่อขัดข้อง')]);
            }

            $healthToken = $responseHealthToken->json('data.access_token');
            if (!$healthToken) {
                return redirect()->route('login')->withErrors(['provider_id' => 'ไม่พบ Access Token ในการยืนยันตัวตนของ Health ID']);
            }

            // 2. Get Provider ID Access Token using Health ID token
            $responseProviderToken = \Illuminate\Support\Facades\Http::timeout(15)
                ->withoutVerifying()
                ->post("{$providerIdUrl}/api/v1/services/token", [
                    'client_id' => $config->provider_id_client_id,
                    'secret_key' => $config->provider_id_secret,
                    'token_by' => 'Health ID',
                    'token' => $healthToken
                ]);

            if (!$responseProviderToken->successful()) {
                Log::error('Provider ID Token Exchange failed: ' . $responseProviderToken->body());
                $errorMsg = $responseProviderToken->json('message') ?: 'ไม่พบข้อมูลบุคลากรทางการแพทย์ หรือไม่มีสิทธิ์เข้าใช้ระบบ';
                return redirect()->route('login')->withErrors(['provider_id' => 'การยืนยันสิทธิ์ Provider ID ล้มเหลว: ' . $errorMsg]);
            }

            $providerToken = $responseProviderToken->json('data.access_token');
            if (!$providerToken) {
                return redirect()->route('login')->withErrors(['provider_id' => 'ไม่ได้รับสิทธิ์ในการเข้าถึงโทเค็นระบบของ Provider ID']);
            }

            // 3. Get Provider Profile
            $responseProfile = \Illuminate\Support\Facades\Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders([
                    'Authorization' => "Bearer {$providerToken}",
                    'client-id' => $config->provider_id_client_id,
                    'secret-key' => $config->provider_id_secret,
                ])
                ->get("{$providerIdUrl}/api/v1/services/profile");

            if (!$responseProfile->successful()) {
                Log::error('Provider Profile retrieval failed: ' . $responseProfile->body());
                return redirect()->route('login')->withErrors(['provider_id' => 'ดึงข้อมูลโปรไฟล์ผู้ใช้งานล้มเหลว: ' . ($responseProfile->json('message') ?: 'การติดต่อขัดข้อง')]);
            }

            $profileData = $responseProfile->json('data');
            $hashCid = $profileData['hash_cid'] ?? null;
            if (!$hashCid) {
                return redirect()->route('login')->withErrors(['provider_id' => 'ไม่พบข้อมูลเลขประจำตัวประชาชนที่เข้าใช้งาน']);
            }

            // 4. Match user in database by hashed username
            $user = User::all()->first(function ($u) use ($hashCid) {
                return hash('sha256', $u->username) === $hashCid;
            });

            if (!$user) {
                return redirect()->route('login')->withErrors(['provider_id' => 'บัญชีผู้ใช้งานของคุณไม่มีสิทธิ์ในระบบนี้ กรุณาติดต่อผู้ดูแลระบบเพื่อขอลงทะเบียนเข้าใช้งาน']);
            }

            if ($user->active !== 'Y') {
                return redirect()->route('login')->withErrors(['provider_id' => 'บัญชีผู้ใช้งานนี้ยังไม่ได้รับการอนุมัติ กรุณารอผู้ดูแลระบบดำเนินการอนุมัติ']);
            }

            // Login
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard')->with('success', 'ยินดีต้อนรับ! เข้าสู่ระบบด้วย Provider ID สำเร็จ');
        } catch (\Throwable $e) {
            Log::error('Provider ID login exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('login')->withErrors(['provider_id' => 'เกิดข้อผิดพลาดในการล็อกอินด้วย Provider ID: ' . $e->getMessage()]);
        }
    }
}
