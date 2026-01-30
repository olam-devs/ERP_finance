<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Particular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('schoolClass')->get();
        return view('students.index', compact('students'));
    }

    public function apiIndex(Request $request)
    {
        $query = Student::with('schoolClass');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('name')->get();
        return response()->json(['students' => $students]);
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('display_order')->get();
        return view('students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_reg_no' => 'required|string|unique:students',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'class_id' => 'required|exists:school_classes,id',
            'parent_phone_1' => 'nullable|string|max:255',
            'parent_phone_2' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,graduated',
        ]);

        // Get class name
        $class = SchoolClass::find($validated['class_id']);
        $validated['class'] = $class->name;

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function show($id)
    {
        $student = Student::with(['schoolClass', 'particulars'])->findOrFail($id);
        return view('students.show', compact('student'));
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classes = SchoolClass::where('is_active', true)->orderBy('display_order')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'student_reg_no' => 'required|string|unique:students,student_reg_no,' . $id,
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'class_id' => 'required|exists:school_classes,id',
            'parent_phone_1' => 'nullable|string|max:255',
            'parent_phone_2' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,graduated',
        ]);

        // Get class name
        $class = SchoolClass::find($validated['class_id']);
        $validated['class'] = $class->name;

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // Don't delete vouchers, just detach particulars
        $student->particulars()->detach();
        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');

        $students = Student::where('name', 'LIKE', "%{$search}%")
            ->orWhere('student_reg_no', 'LIKE', "%{$search}%")
            ->with('schoolClass')
            ->limit(20)
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_reg_no' => $student->student_reg_no,
                    'class' => $student->schoolClass->name ?? $student->class,
                ];
            });

        return response()->json($students);
    }

    // Alias for searchStudents
    public function search(Request $request)
    {
        return $this->searchStudents($request);
    }

    public function apiClasses()
    {
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json($classes);
    }

    public function getStudentPaymentSummary($studentId)
    {
        $student = Student::with('particulars')->findOrFail($studentId);

        $summary = $student->particulars->map(function($particular) {
            return [
                'particular_id' => $particular->id,
                'particular_name' => $particular->name,
                'sales' => $particular->pivot->sales,
                'credit' => $particular->pivot->credit,
                'balance' => $particular->pivot->sales - $particular->pivot->credit,
                'deadline' => $particular->pivot->deadline,
            ];
        });

        return response()->json([
            'student' => $student,
            'particulars' => $summary,
            'total_sales' => $summary->sum('sales'),
            'total_paid' => $summary->sum('credit'),
            'total_balance' => $summary->sum('balance'),
        ]);
    }

    public function downloadStudentTemplate()
    {
        $filename = "student-import-template.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['student_reg_no', 'name', 'gender', 'class_name', 'parent_phone_1', 'parent_phone_2', 'admission_date']);
        fputcsv($handle, ['STU001', 'John Doe Mwangi', 'male', 'Grade 1', '255712345678', '255787654321', '2024-01-15']);
        fputcsv($handle, ['STU002', 'Jane Smith Kamau', 'female', 'Form 1', '255723456789', '', '2024-01-15']);

        fclose($handle);
        exit;
    }

    public function uploadStudentCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($data);

        $imported = 0;
        $parentAccountsCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                $studentData = array_combine($headers, $row);

                // Find or create class
                $class = SchoolClass::where('name', $studentData['class_name'])->first();
                if (!$class) {
                    $errors[] = "Class not found: {$studentData['class_name']} for student {$studentData['name']}";
                    continue;
                }

                $student = Student::updateOrCreate(
                    ['student_reg_no' => $studentData['student_reg_no']],
                    [
                        'name' => $studentData['name'],
                        'gender' => $studentData['gender'],
                        'class_id' => $class->id,
                        'class' => $class->name,
                        'parent_phone_1' => $studentData['parent_phone_1'] ?? null,
                        'parent_phone_2' => $studentData['parent_phone_2'] ?? null,
                        'admission_date' => $studentData['admission_date'] ?? null,
                        'status' => 'active',
                    ]
                );

                // Auto-create parent portal access
                // Parent can login using student registration number only
                // No separate account needed - session-based authentication
                $parentAccountsCreated++;
                
                $imported++;
            }

            DB::commit();
            return response()->json([
                'message' => "Successfully imported {$imported} students. Parent portal access enabled for all students.",
                'imported' => $imported,
                'parent_accounts_created' => $parentAccountsCreated,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Student Promotion
    public function promotionPage()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.accountant.modules.student-promotion', compact('classes'));
    }

    public function getStudentsForPromotion(Request $request)
    {
        $classId = $request->get('source_class_id') ?? $request->get('class_id');

        $students = Student::where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'student_reg_no', 'class_id', 'class']);

        return response()->json(['students' => $students]);
    }

    public function promoteStudents(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'source_class_id' => 'required|exists:school_classes,id',
            'destination_class_id' => 'required|exists:school_classes,id',
        ]);

        // Prevent promoting to the same class
        if ($validated['source_class_id'] == $validated['destination_class_id']) {
            return response()->json([
                'error' => 'Cannot promote students to the same class. Please select a different destination class.'
            ], 400);
        }

        $destinationClass = SchoolClass::find($validated['destination_class_id']);

        DB::beginTransaction();
        try {
            // Only promote students that are actually in the source class
            $updated = Student::whereIn('id', $validated['student_ids'])
                ->where('class_id', $validated['source_class_id'])
                ->update([
                    'class_id' => $destinationClass->id,
                    'class' => $destinationClass->name,
                ]);

            DB::commit();
            return response()->json([
                'message' => $updated . ' students promoted successfully to ' . $destinationClass->name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStudentParticulars($studentId)
    {
        $student = Student::with('particulars')->findOrFail($studentId);

        $particulars = $student->particulars->map(function($particular) {
            return [
                'id' => $particular->id,
                'name' => $particular->name,
                'sales' => $particular->pivot->sales,
                'debit' => $particular->pivot->debit,
                'credit' => $particular->pivot->credit,
                'balance' => $particular->pivot->sales + $particular->pivot->debit - $particular->pivot->credit,
                'deadline' => $particular->pivot->deadline,
            ];
        });

        return response()->json($particulars);
    }

    public function getStudentParticularDetails($studentId, $particularId)
    {
        $student = Student::findOrFail($studentId);
        $particular = $student->particulars()->where('particular_id', $particularId)->first();

        if (!$particular) {
            return response()->json(['error' => 'Particular not assigned to student'], 404);
        }

        return response()->json([
            'particular_id' => $particular->id,
            'particular_name' => $particular->name,
            'sales' => $particular->pivot->sales,
            'debit' => $particular->pivot->debit,
            'credit' => $particular->pivot->credit,
            'balance' => $particular->pivot->sales + $particular->pivot->debit - $particular->pivot->credit,
            'deadline' => $particular->pivot->deadline,
        ]);
    }
}
