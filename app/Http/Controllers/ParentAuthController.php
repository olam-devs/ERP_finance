<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolSetting;
use App\Services\ActivityLogger;
use App\Traits\HasSchoolContext;
use Illuminate\Support\Facades\Session;

class ParentAuthController extends Controller
{
    use HasSchoolContext;

    protected ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function showLogin()
    {
        if (Session::has('parent_student_id')) {
            return redirect()->route('parent.dashboard');
        }

        $school = SchoolSetting::getSettings();
        return view('parent.login', compact('school'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'student_reg_no' => 'required|string',
        ]);

        $regNo = $request->student_reg_no;

        $student = Student::where('student_reg_no', $regNo)->first();

        if (!$student) {
            return back()->withInput()->with('error', 'Student Registration Number not found.');
        }

        // Login success - simplified authentication
        Session::put('parent_student_id', $student->id);
        Session::put('parent_student_name', $student->name);
        Session::put('parent_language', $request->input('language', 'en')); // Default to English

        // Log parent login
        $schoolId = $this->getSchoolId();
        if ($schoolId) {
            $this->activityLogger->logParentAction(
                $schoolId,
                $student->id,
                $student->name,
                'login',
                "Parent portal login for student: {$student->name} ({$student->student_reg_no})"
            );
        }

        return redirect()->route('parent.dashboard');
    }

    public function logout()
    {
        // Log parent logout before clearing session
        $schoolId = $this->getSchoolId();
        $studentId = Session::get('parent_student_id');
        $studentName = Session::get('parent_student_name');

        if ($schoolId && $studentId) {
            $this->activityLogger->logParentAction(
                $schoolId,
                $studentId,
                $studentName ?? 'Unknown',
                'logout',
                "Parent portal logout for student: {$studentName}"
            );
        }

        Session::forget(['parent_student_id', 'parent_student_name', 'parent_language']);
        return redirect()->route('parent.login');
    }
}
