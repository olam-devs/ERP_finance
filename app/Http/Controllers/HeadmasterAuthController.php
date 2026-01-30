<?php

namespace App\Http\Controllers;

use App\Models\Headmaster;
use App\Services\ActivityLogger;
use App\Traits\HasSchoolContext;
use Illuminate\Http\Request;

class HeadmasterAuthController extends Controller
{
    use HasSchoolContext;

    protected ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Show headmaster login form.
     */
    public function showLogin()
    {
        if (session('headmaster_id')) {
            return redirect()->route('headmaster.dashboard');
        }

        return view('headmaster.login');
    }

    /**
     * Handle headmaster login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string',
        ]);

        $headmaster = Headmaster::where('registration_number', $request->registration_number)
            ->where('is_active', true)
            ->first();

        if (!$headmaster) {
            return back()->withErrors([
                'registration_number' => 'Invalid registration number or account is inactive.',
            ])->withInput();
        }

        // Store headmaster ID in session
        session([
            'headmaster_id' => $headmaster->id,
            'headmaster_name' => $headmaster->name,
        ]);

        // Log headmaster login
        $schoolId = $this->getSchoolId();
        if ($schoolId) {
            $this->activityLogger->logHeadmasterPortalAction(
                $schoolId,
                $headmaster->id,
                $headmaster->name,
                'login',
                "Headmaster portal login: {$headmaster->name} ({$headmaster->registration_number})"
            );
        }

        return redirect()->route('headmaster.dashboard')
            ->with('success', "Welcome back, {$headmaster->name}!");
    }

    /**
     * Handle headmaster logout.
     */
    public function logout(Request $request)
    {
        // Log headmaster logout before clearing session
        $schoolId = $this->getSchoolId();
        $headmasterId = session('headmaster_id');
        $headmasterName = session('headmaster_name');

        if ($schoolId && $headmasterId) {
            $this->activityLogger->logHeadmasterPortalAction(
                $schoolId,
                $headmasterId,
                $headmasterName ?? 'Unknown',
                'logout',
                "Headmaster portal logout: {$headmasterName}"
            );
        }

        session()->forget(['headmaster_id', 'headmaster_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('headmaster.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
