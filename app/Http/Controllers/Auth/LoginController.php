<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt(array_merge($credentials, ['active' => 'Y']), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        // Check if user exists but is inactive
        $user = \App\Models\User::where('username', $request->username)->first();
        if ($user && $user->active !== 'Y') {
            return back()->withErrors([
                'username' => 'บัญชีของคุณยังไม่ได้รับการอนุมัติการใช้งาน กรุณารอผู้ดูแลระบบอนุมัติ',
            ])->onlyInput('username');
        }

        return back()->withErrors([
            'username' => 'Username หรือ รหัสผ่าน ไม่ถูกต้อง',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
