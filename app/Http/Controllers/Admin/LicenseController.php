<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseProgram;
use App\Models\LicenseModule;
use App\Models\LicenseModuleActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class LicenseController extends Controller
{
    /**
     * Display a listing of programs and licenses.
     */
    public function index(Request $request)
    {
        // Enforce user limitation
        if (auth()->user()->username !== '1341800003078') {
            abort(403, 'Unauthorized.');
        }

        // Auto Sync: อัปเดตสถานะคีย์ที่หมดอายุแล้วในฐานข้อมูลเป็น expired
        License::whereIn('status', ['active', 'pending'])
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        $programs = LicenseProgram::withCount('licenses')->with('modules')->get();
        $licenses = License::with(['program', 'activatedModules'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('license_key', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('hcode', 'like', "%{$search}%")
                    ->orWhere('hardware_id', 'like', "%{$search}%");
            })
            ->when($request->filled('program_id'), function ($query) use ($request) {
                $query->where('program_id', $request->input('program_id'));
            })
            ->latest()
            ->paginate(20);

        return view('admin.licenses.index', compact('programs', 'licenses'));
    }

    /**
     * Store a newly created program.
     */
    public function storeProgram(Request $request)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:191|unique:license_programs,code',
            'description' => 'nullable|string',
            'language' => 'required|string|in:go,laravel,python,csharp',
        ]);

        LicenseProgram::create($request->only('name', 'code', 'description', 'language'));

        return redirect()->back()->with('success', 'เพิ่มระบบ/โปรแกรมใหม่สำเร็จ');
    }

    /**
     * Update program details.
     */
    public function updateProgram(Request $request, $id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $program = LicenseProgram::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:191|unique:license_programs,code,' . $program->id,
            'description' => 'nullable|string',
            'language' => 'required|string|in:go,laravel,python,csharp',
        ]);

        $program->update($request->only('name', 'code', 'description', 'language'));

        return redirect()->back()->with('success', 'แก้ไขข้อมูลระบบสำเร็จ');
    }

    /**
     * Delete a program.
     */
    public function destroyProgram($id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $program = LicenseProgram::findOrFail($id);

        if ($program->licenses()->exists()) {
            return redirect()->back()->with('error', 'ไม่สามารถลบโปรแกรมได้เนื่องจากมี License ค้างอยู่ในระบบ');
        }

        $program->delete();
        return redirect()->back()->with('success', 'ลบโปรแกรมสำเร็จ');
    }

    /**
     * Store a newly created license.
     */
    public function storeLicense(Request $request)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $request->validate([
            'program_id' => 'required|exists:license_programs,id',
            'customer_name' => 'required|string|max:191',
            'hcode' => 'nullable|string|max:50',
            'hardware_id' => 'nullable|string|max:191',
            'license_type' => 'required|in:full,module',
            'status' => 'required|in:active,suspended,expired,pending',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'modules' => 'nullable|array',
            'modules.*.active' => 'nullable|in:1',
            'modules.*.status' => 'nullable|in:active,suspended,expired,pending',
            'modules.*.expired_at' => 'nullable|date',
        ]);

        $program = LicenseProgram::findOrFail($request->program_id);
        $cleanPrefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $program->code));
        $prefix = substr($cleanPrefix, 0, 6) ?: 'KEY';

        $licenseKey = License::generateKey($prefix);

        $status = $request->status;
        $expiredAt = $request->expired_at ? Carbon::parse($request->expired_at)->endOfDay() : null;

        if ($expiredAt) {
            if ($expiredAt->isPast()) {
                $status = 'expired';
            } elseif ($status === 'expired') {
                $status = 'active';
            }
        }

        $license = License::create([
            'program_id' => $request->program_id,
            'license_key' => $licenseKey,
            'customer_name' => $request->customer_name,
            'hcode' => $request->hcode,
            'hardware_id' => $request->hardware_id,
            'license_type' => $request->license_type,
            'status' => $status,
            'expired_at' => $expiredAt,
            'notes' => $request->notes,
        ]);

        if ($license->license_type === 'module' && $request->has('modules')) {
            foreach ($request->input('modules') as $moduleId => $moduleData) {
                if (isset($moduleData['active']) && $moduleData['active'] == '1') {
                    $modExpiredAt = !empty($moduleData['expired_at']) ? Carbon::parse($moduleData['expired_at'])->endOfDay() : null;
                    LicenseModuleActivation::create([
                        'license_id' => $license->id,
                        'module_id' => $moduleId,
                        'status' => $moduleData['status'] ?? 'active',
                        'expired_at' => $modExpiredAt,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'สร้าง License Key สำเร็จ: ' . $licenseKey);
    }

    /**
     * Update license details.
     */
    public function updateLicense(Request $request, $id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $license = License::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:191',
            'hcode' => 'nullable|string|max:50',
            'hardware_id' => 'nullable|string|max:191',
            'license_type' => 'required|in:full,module',
            'status' => 'required|in:active,suspended,expired,pending',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'modules' => 'nullable|array',
            'modules.*.active' => 'nullable|in:1',
            'modules.*.status' => 'nullable|in:active,suspended,expired,pending',
            'modules.*.expired_at' => 'nullable|date',
        ]);

        $status = $request->status;
        $expiredAt = $request->expired_at ? Carbon::parse($request->expired_at)->endOfDay() : null;

        if ($expiredAt) {
            if ($expiredAt->isPast()) {
                $status = 'expired';
            } elseif ($status === 'expired') {
                $status = 'active';
            }
        }

        $license->update([
            'customer_name' => $request->customer_name,
            'hcode' => $request->hcode,
            'hardware_id' => $request->hardware_id,
            'license_type' => $request->license_type,
            'status' => $status,
            'expired_at' => $expiredAt,
            'notes' => $request->notes,
        ]);

        // Sync modules
        LicenseModuleActivation::where('license_id', $license->id)->delete();
        if ($license->license_type === 'module' && $request->has('modules')) {
            foreach ($request->input('modules') as $moduleId => $moduleData) {
                if (isset($moduleData['active']) && $moduleData['active'] == '1') {
                    $modExpiredAt = !empty($moduleData['expired_at']) ? Carbon::parse($moduleData['expired_at'])->endOfDay() : null;
                    LicenseModuleActivation::create([
                        'license_id' => $license->id,
                        'module_id' => $moduleId,
                        'status' => $moduleData['status'] ?? 'active',
                        'expired_at' => $modExpiredAt,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'อัปเดต License สำเร็จ');
    }

    /**
     * Store program module
     */
    public function storeModule(Request $request, $program_id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $request->validate([
            'code' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $program = LicenseProgram::findOrFail($program_id);

        $exists = LicenseModule::where('program_id', $program->id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'มีรหัสโมดูลนี้ในระบบนี้อยู่แล้ว');
        }

        LicenseModule::create([
            'program_id' => $program->id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'เพิ่มโมดูลใหม่สำเร็จ');
    }

    /**
     * Destroy program module
     */
    public function destroyModule($id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $module = LicenseModule::findOrFail($id);
        
        // Remove activations
        LicenseModuleActivation::where('module_id', $module->id)->delete();
        
        $module->delete();

        return redirect()->back()->with('success', 'ลบโมดูลสำเร็จ');
    }

    /**
     * Delete a license.
     */
    public function destroyLicense($id)
    {
        if (auth()->user()->username !== '1341800003078') {
            abort(403);
        }

        $license = License::findOrFail($id);
        $license->delete();

        return redirect()->back()->with('success', 'ลบ License สำเร็จ');
    }

    /**
     * Public API endpoint to verify license.
     * Request: license_key, program_code, hardware_id, hcode, domain, ip
     */
    public function verify(Request $request)
    {
        $licenseKey = $request->input('license_key');
        $programCode = $request->input('program_code');
        $hardwareId = $request->input('hardware_id');
        $hcode = $request->input('hcode');

        if (!$licenseKey || !$programCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing license_key or program_code parameter.'
            ], 400);
        }

        // Find program
        $program = LicenseProgram::where('code', $programCode)->first();
        if (!$program) {
            return response()->json([
                'status' => 'invalid_program',
                'message' => 'Program code is not registered.'
            ], 404);
        }

        // Find license
        $license = License::where('license_key', $licenseKey)
            ->where('program_id', $program->id)
            ->first();

        if (!$license) {
            return response()->json([
                'status' => 'invalid_key',
                'message' => 'License key is invalid for this program.'
            ], 404);
        }

        // Check if pending
        if ($license->status === 'pending') {
            return response()->json([
                'status' => 'pending',
                'message' => 'This license is pending activation.',
                'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
            ], 403);
        }

        // Check if suspended
        if ($license->status === 'suspended') {
            return response()->json([
                'status' => 'suspended',
                'message' => 'This license has been suspended.',
                'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
            ], 403);
        }

        // Check if expired
        if ($license->status === 'expired' || $license->isExpired()) {
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
            }
            return response()->json([
                'status' => 'expired',
                'message' => 'This license has expired.',
                'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
            ], 403);
        }

        // Validate Hospital Code (hcode) if bound in DB
        if (!empty($license->hcode) && !empty($hcode) && $license->hcode !== $hcode) {
            return response()->json([
                'status' => 'invalid_hcode',
                'message' => 'This license is registered to another organization code.',
                'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
            ], 403);
        }

        // Validate Hardware ID if bound in DB
        if (!empty($license->hardware_id)) {
            if (!empty($hardwareId) && $license->hardware_id !== $hardwareId) {
                return response()->json([
                    'status' => 'invalid_hardware',
                    'message' => 'This license is bound to a different machine.',
                    'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
                ], 403);
            }
        } else {
            // Auto-bind hardware ID on first check if client provided it
            if (!empty($hardwareId)) {
                $license->update([
                    'hardware_id' => $hardwareId,
                    'activated_at' => Carbon::now(),
                    'status' => 'active'
                ]);
            }
        }

        // Update activated_at and status if empty or pending
        if (is_null($license->activated_at) || $license->status === 'pending') {
            $license->update([
                'activated_at' => $license->activated_at ?: Carbon::now(),
                'status' => 'active'
            ]);
        }

        // Fetch modules depending on license type
        $modules = [];
        $moduleDetails = [];
        if (($license->license_type ?? 'full') === 'full') {
            $programModules = $program->modules()->get();
            $modules = $programModules->pluck('code')->toArray();
            foreach ($programModules as $mod) {
                $moduleDetails[] = [
                    'code' => $mod->code,
                    'name' => $mod->name,
                    'status' => 'active',
                    'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
                ];
            }
        } else {
            $activated = $license->activatedModules()->withPivot('status', 'expired_at')->get();
            foreach ($activated as $mod) {
                $moduleDetails[] = [
                    'code' => $mod->code,
                    'name' => $mod->name,
                    'status' => $mod->pivot->status,
                    'expired_at' => $mod->pivot->expired_at ? Carbon::parse($mod->pivot->expired_at)->toDateString() : null,
                ];

                // Only put in active modules list if active and not expired
                $isExpired = $mod->pivot->expired_at && Carbon::parse($mod->pivot->expired_at)->isPast();
                if ($mod->pivot->status === 'active' && !$isExpired) {
                    $modules[] = $mod->code;
                }
            }
        }

        // Generate tamper-proof Digital Signature
        $expiryStr = $license->expired_at ? $license->expired_at->toIso8601String() : 'never';
        $modulesStr = implode(',', $modules);
        $signaturePayload = implode('|', [
            $license->license_key,
            $program->code,
            $license->status,
            $expiryStr,
            $license->hardware_id ?? 'any',
            $license->hcode ?? 'any',
            $license->license_type ?? 'full',
            $modulesStr
        ]);

        $signature = hash_hmac('sha256', $signaturePayload, config('app.key') ?: 'smartdata-secret-key-fallback');

        return response()->json([
            'status' => $license->status,
            'license_type' => $license->license_type ?? 'full',
            'modules' => $modules,
            'module_details' => $moduleDetails,
            'license_key' => $license->license_key,
            'program_name' => $program->name,
            'customer_name' => $license->customer_name,
            'hcode' => $license->hcode,
            'hardware_id' => $license->hardware_id,
            'activated_at' => $license->activated_at ? $license->activated_at->toIso8601String() : null,
            'expired_at' => $license->expired_at ? $license->expired_at->toDateString() : null,
            'expires_in_days' => $license->expired_at ? Carbon::now()->diffInDays($license->expired_at, false) : null,
            'signature' => $signature,
            'verified_at' => Carbon::now()->toIso8601String()
        ]);
    }

    /**
     * Public API endpoint to request activation (pending license).
     * Request: program_code, hardware_id, customer_name, customer_email, notes
     */
    public function requestActivation(Request $request)
    {
        $programCode = $request->input('program_code');
        $hardwareId = $request->input('hardware_id');
        $customerName = $request->input('customer_name');
        $customerEmail = $request->input('customer_email');
        $notes = $request->input('notes');

        if (!$programCode || !$hardwareId || !$customerName) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing program_code, hardware_id, or customer_name parameter.'
            ], 400);
        }

        $program = LicenseProgram::where('code', $programCode)->first();
        if (!$program) {
            return response()->json([
                'status' => 'invalid_program',
                'message' => 'Program code is not registered.'
            ], 404);
        }

        // Check if a request or license for this hardware_id and program already exists
        $existing = License::where('program_id', $program->id)
            ->where('hardware_id', $hardwareId)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'exists',
                'message' => 'An activation request or license already exists for this device.',
                'license_key' => $existing->license_key,
                'license_status' => $existing->status
            ], 200);
        }

        // Create pending license request
        $cleanPrefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $program->code));
        $prefix = substr($cleanPrefix, 0, 6) ?: 'KEY';
        $licenseKey = License::generateKey($prefix);

        $license = License::create([
            'program_id' => $program->id,
            'license_key' => $licenseKey,
            'customer_name' => $customerName,
            'hardware_id' => $hardwareId,
            'status' => 'pending',
            'notes' => implode("\n", array_filter([
                "ส่งคำขอจาก Client App (Go Program)",
                "Email: " . $customerEmail,
                $notes
            ]))
        ]);

        return response()->json([
            'status' => 'pending',
            'message' => 'Activation request received. Please notify admin to activate.',
            'license_key' => $licenseKey
        ], 201);
    }
}
