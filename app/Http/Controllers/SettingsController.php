<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::getSettings();
        return view('admin.accountant.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'po_box' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = SchoolSetting::first() ?? new SchoolSetting();

        $settings->school_name = $validated['school_name'];
        $settings->po_box = $validated['po_box'] ?? null;
        $settings->region = $validated['region'] ?? null;
        $settings->phone = $validated['phone'] ?? null;
        $settings->email = $validated['email'] ?? null;

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::exists($settings->logo_path)) {
                Storage::delete($settings->logo_path);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo_path = $logoPath;
        }

        $settings->save();

        return redirect()->route('accountant.settings')
            ->with('success', 'Settings updated successfully!');
    }
}
