<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
            'active' => 'nullable',
            'allow_hosxp_report' => 'nullable|string|in:Y,N',
            'allow_asset' => 'nullable|string|in:Y,N',
            'allow_warehouse' => 'nullable|string|in:Y,N',
            'allow_personnel' => 'nullable|string|in:Y,N',
            'allow_incident' => 'nullable|string|in:Y,N',
            'allow_skpcard' => 'nullable|string|in:Y,N',
            'allow_lend' => 'nullable|string|in:Y,N',
            'allow_mra' => 'nullable|string|in:Y,N',
            'allow_moph_alert' => 'nullable|string|in:Y,N',
            'allow_copilot' => 'nullable|string|in:Y,N',
        ]);

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => $request->active == '1' ? 'Y' : 'N',
            'allow_hosxp_report' => $request->has('allow_hosxp_report') ? 'Y' : 'N',
            'allow_asset' => $request->has('allow_asset') ? 'Y' : 'N',
            'allow_warehouse' => $request->has('allow_warehouse') ? 'Y' : 'N',
            'allow_personnel' => $request->has('allow_personnel') ? 'Y' : 'N',
            'allow_incident' => $request->has('allow_incident') ? 'Y' : 'N',
            'allow_skpcard' => $request->has('allow_skpcard') ? 'Y' : 'N',
            'allow_lend' => $request->has('allow_lend') ? 'Y' : 'N',
            'allow_mra' => $request->has('allow_mra') ? 'Y' : 'N',
            'allow_moph_alert' => $request->has('allow_moph_alert') ? 'Y' : 'N',
            'allow_copilot' => $request->has('allow_copilot') ? 'Y' : 'N',
        ];

        // Ensure we only pass attributes that actually exist in the table columns
        try {
            $columns = Schema::getColumnListing('users');
            $userData = array_intersect_key($userData, array_flip($columns));
        } catch (\Throwable $e) {
            // Fallback: proceed with data as is
        }

        User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,user',
            'active' => 'nullable',
            'allow_hosxp_report' => 'nullable|string|in:Y,N',
            'allow_asset' => 'nullable|string|in:Y,N',
            'allow_warehouse' => 'nullable|string|in:Y,N',
            'allow_personnel' => 'nullable|string|in:Y,N',
            'allow_incident' => 'nullable|string|in:Y,N',
            'allow_skpcard' => 'nullable|string|in:Y,N',
            'allow_lend' => 'nullable|string|in:Y,N',
            'allow_mra' => 'nullable|string|in:Y,N',
            'allow_moph_alert' => 'nullable|string|in:Y,N',
            'allow_copilot' => 'nullable|string|in:Y,N',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'role' => $request->role,
            'active' => $request->active == '1' ? 'Y' : 'N',
            'allow_hosxp_report' => $request->has('allow_hosxp_report') ? 'Y' : 'N',
            'allow_asset' => $request->has('allow_asset') ? 'Y' : 'N',
            'allow_warehouse' => $request->has('allow_warehouse') ? 'Y' : 'N',
            'allow_personnel' => $request->has('allow_personnel') ? 'Y' : 'N',
            'allow_incident' => $request->has('allow_incident') ? 'Y' : 'N',
            'allow_skpcard' => $request->has('allow_skpcard') ? 'Y' : 'N',
            'allow_lend' => $request->has('allow_lend') ? 'Y' : 'N',
            'allow_mra' => $request->has('allow_mra') ? 'Y' : 'N',
            'allow_moph_alert' => $request->has('allow_moph_alert') ? 'Y' : 'N',
            'allow_copilot' => $request->has('allow_copilot') ? 'Y' : 'N',
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Ensure we only update attributes that actually exist in the table columns
        try {
            $columns = Schema::getColumnListing('users');
            $updateData = array_intersect_key($updateData, array_flip($columns));
        } catch (\Throwable $e) {
            // Fallback: proceed with data as is
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Reset the user's password to 12345678.
     */
    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('12345678')
        ]);

        return redirect()->route('admin.users.index')->with('success', 'เปลี่ยนรหัสผ่านผู้ใช้เรียบร้อยแล้ว (รหัสผ่านใหม่คือ 12345678)');
    }
}

