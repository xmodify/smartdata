<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TelegramNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'active' => 'N', // Set to N (Inactive) by default
        ]);

        // Send Telegram Notification
        try {
            $token = TelegramNotify::where('name', 'telegram_bot_token')->value('value');
            $chatIdsString = TelegramNotify::where('name', 'telegram_chat_id_register')->value('value');

            if ($token && $chatIdsString && $token !== 'xxx' && $chatIdsString !== 'xxx') {
                $message = "🔔 <b>มีการลงทะเบียนใหม่</b>\n\n"
                    . "ชื่อ: {$user->name}\n"
                    . "Username: {$user->username}\n"
                    . "Email: {$user->email}\n"
                    . "เวลา: " . now()->format('Y-m-d H:i:s') . "\n\n"
                    . "กรุณาเข้าตรวจสอบและอนุมัติการใช้งานในระบบ\n"
                    . "👉 <a href='" . route('admin.dashboard') . "'>คลิกเพื่อจัดการผู้ใช้งาน</a>";

                $chatIds = array_map('trim', explode(',', $chatIdsString));
                foreach ($chatIds as $chatId) {
                    if (empty($chatId))
                        continue;
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'HTML',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Telegram Notification Error: ' . $e->getMessage());
        }

        return redirect()->route('login')->with('success', 'ลงทะเบียนสำเร็จ! กรุณารอผู้ดูแลระบบอนุมัติการใช้งาน');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 
                'string', 
                'regex:/^[0-9]{13}$/', 
                'unique:users',
                function ($attribute, $value, $fail) {
                    try {
                        $exists = \Illuminate\Support\Facades\DB::connection('backoffice')
                            ->table('hrd_person')
                            ->where('HR_CID', $value)
                            ->where('HR_STATUS_ID', 1)
                            ->exists();
                        if (!$exists) {
                            $fail('คุณไม่ใช่เจ้าหน้าที่โรงพยาบาลหัวตะพาน โปรดติดต่อ Admin');
                        }
                    } catch (\Exception $e) {
                        Log::error('Backoffice Database Connection Error during registration: ' . $e->getMessage());
                        $fail('ระบบขัดข้องไม่สามารถตรวจสอบข้อมูลเจ้าหน้าที่ได้ในขณะนี้ โปรดติดต่อ Admin');
                    }
                }
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.regex' => 'Username ต้องเป็นเลขบัตรประชาชน 13 หลักเท่านั้น',
            'username.unique' => 'Username นี้ถูกใช้งานแล้ว',
        ]);
    }
}
