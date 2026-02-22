<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');

        return inertia('Admin/Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|array',
            'settings.*.id' => 'required|exists:system_settings,id',
            'settings.*.value' => 'required'
        ]);

        foreach ($validated['settings'] as $settingData) {
            $setting = SystemSetting::find($settingData['id']);
            // Convert string boolean "false" back to actual boolean string if needed, 
            // but the model will handle raw string storage and getVal parses it.
            $setting->update(['value' => (string) $settingData['value']]);
        }

        return redirect()->back()->with('success', 'Hệ thống đã cập nhật cấu hình Core thành công!');
    }
}
