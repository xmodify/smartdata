<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SysVar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
            'active' => 'required|boolean',
            'allow_asset' => 'nullable|string|in:Y,N',
            'allow_personnel' => 'nullable|string|in:Y,N',
            'allow_incident' => 'nullable|string|in:Y,N',
            'allow_skpcard' => 'nullable|string|in:Y,N',
            'allow_audit' => 'nullable|string|in:Y,N',
        ]);

        $validated['allow_asset'] = $request->has('allow_asset') ? 'Y' : 'N';
        $validated['allow_personnel'] = $request->has('allow_personnel') ? 'Y' : 'N';
        $validated['allow_incident'] = $request->has('allow_incident') ? 'Y' : 'N';
        $validated['allow_skpcard'] = $request->has('allow_skpcard') ? 'Y' : 'N';
        $validated['allow_audit'] = $request->has('allow_audit') ? 'Y' : 'N';

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,user',
            'active' => 'required|boolean',
            'allow_asset' => 'nullable|string|in:Y,N',
            'allow_personnel' => 'nullable|string|in:Y,N',
            'allow_incident' => 'nullable|string|in:Y,N',
            'allow_skpcard' => 'nullable|string|in:Y,N',
            'allow_audit' => 'nullable|string|in:Y,N',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['allow_asset'] = $request->has('allow_asset') ? 'Y' : 'N';
        $validated['allow_personnel'] = $request->has('allow_personnel') ? 'Y' : 'N';
        $validated['allow_incident'] = $request->has('allow_incident') ? 'Y' : 'N';
        $validated['allow_skpcard'] = $request->has('allow_skpcard') ? 'Y' : 'N';
        $validated['allow_audit'] = $request->has('allow_audit') ? 'Y' : 'N';

        $user->update($validated);

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
}
