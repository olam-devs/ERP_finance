<?php

namespace App\Http\Controllers;

use App\Models\Particular;
use App\Models\Student;
use App\Models\Voucher;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticularController extends Controller
{
    public function index()
    {
        $particulars = Particular::with('students')->get();
        return response()->json($particulars);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'book_ids' => 'required|array',
            'class_names' => 'nullable|array',
        ]);

        $particular = Particular::create($validated);
        return response()->json($particular, 201);
    }

    public function show($id)
    {
        $particular = Particular::with('students')->findOrFail($id);
        return response()->json($particular);
    }

    public function update(Request $request, $id)
    {
        $particular = Particular::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'book_ids' => 'required|array',
            'class_names' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $particular->update($validated);
        return response()->json($particular);
    }

    public function destroy($id)
    {
        $particular = Particular::findOrFail($id);
        $particular->students()->detach();
        $particular->delete();
        return response()->json(['message' => 'Particular deleted successfully']);
    }

    public function assignStudents(Request $request, $id)
    {
        $particular = Particular::findOrFail($id);

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'sales' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['student_ids'] as $studentId) {
                // Check if already assigned for this academic year
                $existingAssignment = $particular->students()
                    ->where('student_id', $studentId)
                    ->wherePivot('academic_year_id', $validated['academic_year_id'])
                    ->exists();

                if (!$existingAssignment) {
                    // Create pivot entry
                    $particular->students()->attach($studentId, [
                        'sales' => $validated['sales'],
                        'deadline' => $validated['deadline'] ?? null,
                        'debit' => 0,
                        'credit' => 0,
                        'overpayment' => 0,
                        'academic_year_id' => $validated['academic_year_id'],
                    ]);

                    // Create Sales voucher for ledger visibility
                    if ($validated['sales'] > 0) {
                        Voucher::create([
                            'date' => now(),
                            'student_id' => $studentId,
                            'particular_id' => $id,
                            'book_id' => null,
                            'voucher_type' => 'Sales',
                            'debit' => $validated['sales'],
                            'credit' => 0,
                            'notes' => "Fee assignment: {$particular->name}",
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'Students assigned successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkOpeningBalance(Request $request, $id)
    {
        $particular = Particular::findOrFail($id);

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.student_id' => 'required|exists:students,id',
            'assignments.*.sales' => 'required|numeric|min:0',
            'assignments.*.deadline' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                $studentId = $assignment['student_id'];

                // Check if already assigned for this academic year
                $existingAssignment = $particular->students()
                    ->where('student_id', $studentId)
                    ->wherePivot('academic_year_id', $validated['academic_year_id'])
                    ->exists();

                if (!$existingAssignment) {
                    // Create pivot entry
                    $particular->students()->attach($studentId, [
                        'sales' => $assignment['sales'],
                        'deadline' => $assignment['deadline'] ?? null,
                        'debit' => 0,
                        'credit' => 0,
                        'overpayment' => 0,
                        'academic_year_id' => $validated['academic_year_id'],
                    ]);

                    // Create Sales voucher for ledger visibility
                    if ($assignment['sales'] > 0) {
                        Voucher::create([
                            'date' => now(),
                            'student_id' => $studentId,
                            'particular_id' => $id,
                            'book_id' => null,
                            'voucher_type' => 'Sales',
                            'debit' => $assignment['sales'],
                            'credit' => 0,
                            'notes' => "Bulk fee assignment: {$particular->name}",
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'Bulk assignment completed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get only students who have been assigned to this particular
    public function getExistingAssignments($id, Request $request)
    {
        $particular = Particular::findOrFail($id);
        $academicYearId = $request->get('academic_year_id');

        // Get students query - filter by academic year if provided
        $query = $particular->students()->with('schoolClass');

        if ($academicYearId) {
            $query->wherePivot('academic_year_id', $academicYearId);
        }

        $assignedStudents = $query->get()
            ->map(function($student) {
                $academicYear = null;
                if ($student->pivot->academic_year_id) {
                    $academicYear = AcademicYear::find($student->pivot->academic_year_id);
                }

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_reg_no' => $student->student_reg_no,
                    'class_name' => $student->schoolClass->name ?? $student->class,
                    'sales' => (float)($student->pivot->sales ?? 0),
                    'debit' => (float)($student->pivot->debit ?? 0),
                    'credit' => (float)($student->pivot->credit ?? 0),
                    'balance' => (float)(($student->pivot->sales ?? 0) + ($student->pivot->debit ?? 0) - ($student->pivot->credit ?? 0)),
                    'deadline' => $student->pivot->deadline,
                    'academic_year_id' => $student->pivot->academic_year_id,
                    'academic_year_name' => $academicYear ? $academicYear->name : 'N/A',
                ];
            });

        return response()->json([
            'particular_name' => $particular->name,
            'assignments' => $assignedStudents,
            'total_assignments' => $assignedStudents->count(),
        ]);
    }

    public function getStudentsForNewAssignment($id, Request $request)
    {
        $particular = Particular::findOrFail($id);
        $academicYearId = $request->get('academic_year_id');

        // Get all active students with their assignment status
        $students = Student::with('schoolClass')
            ->where('status', 'active')
            ->get()
            ->map(function($student) use ($particular, $academicYearId) {
                // Check if student has this particular assigned for the specified academic year
                $query = $student->particulars()
                    ->where('particular_id', $particular->id);

                if ($academicYearId) {
                    $query->wherePivot('academic_year_id', $academicYearId);
                }

                $assignment = $query->first();

                if ($assignment) {
                    // Student already has this particular for the academic year
                    return [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'student_reg_no' => $student->student_reg_no,
                        'class_name' => $student->schoolClass->name ?? $student->class,
                        'has_assignment' => true,
                        'sales' => (float)($assignment->pivot->sales ?? 0),
                        'debit' => (float)($assignment->pivot->debit ?? 0),
                        'credit' => (float)($assignment->pivot->credit ?? 0),
                        'balance' => (float)(($assignment->pivot->sales ?? 0) + ($assignment->pivot->debit ?? 0) - ($assignment->pivot->credit ?? 0)),
                        'deadline' => $assignment->pivot->deadline,
                        'academic_year_id' => $assignment->pivot->academic_year_id,
                    ];
                } else {
                    // Student doesn't have this particular yet for the academic year
                    return [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'student_reg_no' => $student->student_reg_no,
                        'class_name' => $student->schoolClass->name ?? $student->class,
                        'has_assignment' => false,
                        'sales' => 0,
                        'debit' => 0,
                        'credit' => 0,
                        'balance' => 0,
                        'deadline' => null,
                        'academic_year_id' => null,
                    ];
                }
            });

        return response()->json($students);
    }

    public function createAssignment(Request $request, $particularId)
    {
        $particular = Particular::findOrFail($particularId);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'sales' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Check if assignment already exists for this academic year
        $existingAssignment = $particular->students()
            ->where('student_id', $validated['student_id'])
            ->wherePivot('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($existingAssignment) {
            return response()->json(['error' => 'This student already has this particular assigned for this academic year'], 400);
        }

        DB::beginTransaction();
        try {
            // Create new assignment
            $particular->students()->attach($validated['student_id'], [
                'sales' => $validated['sales'],
                'debit' => 0,
                'credit' => 0,
                'overpayment' => 0,
                'deadline' => $validated['deadline'] ?? null,
                'academic_year_id' => $validated['academic_year_id'],
            ]);

            // Create Sales voucher for ledger visibility
            if ($validated['sales'] > 0) {
                Voucher::create([
                    'date' => now(),
                    'student_id' => $validated['student_id'],
                    'particular_id' => $particularId,
                    'book_id' => null,
                    'voucher_type' => 'Sales',
                    'debit' => $validated['sales'],
                    'credit' => 0,
                    'notes' => "Fee assignment: {$particular->name}",
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Assignment created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateAssignment(Request $request, $particularId, $studentId)
    {
        $particular = Particular::findOrFail($particularId);
        $student = Student::findOrFail($studentId);

        $validated = $request->validate([
            'sales' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $academicYearId = $validated['academic_year_id'] ?? null;

        DB::beginTransaction();
        try {
            // Get current sales amount - filter by academic year if provided
            $query = $particular->students()->where('student_id', $studentId);

            if ($academicYearId) {
                $query->wherePivot('academic_year_id', $academicYearId);
            }

            $currentAssignment = $query->first();

            if (!$currentAssignment) {
                return response()->json(['error' => 'Assignment not found'], 404);
            }

            $oldSales = $currentAssignment->pivot->sales ?? 0;
            $newSales = $validated['sales'];
            $difference = $newSales - $oldSales;

            // Build update data
            $updateData = [
                'sales' => $newSales,
                'deadline' => $validated['deadline'] ?? null,
            ];

            // Update the assignment using direct DB query to filter by academic_year_id
            if ($academicYearId) {
                DB::connection('tenant')->table('particular_student')
                    ->where('particular_id', $particularId)
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $academicYearId)
                    ->update($updateData);
            } else {
                $particular->students()->updateExistingPivot($studentId, $updateData);
            }

            // If amount changed, create a voucher entry
            if ($difference != 0) {
                $voucher = new Voucher();
                $voucher->date = now();
                $voucher->student_id = $studentId;
                $voucher->particular_id = $particularId;
                $voucher->book_id = null;
                $voucher->voucher_type = 'Sales';
                $voucher->created_by = auth()->id();

                if ($difference > 0) {
                    $voucher->debit = $difference;
                    $voucher->credit = 0;
                    $voucher->notes = "Sales amount increased from {$oldSales} to {$newSales} for {$particular->name}";

                    $currentDebit = $currentAssignment->pivot->debit ?? 0;
                    $updateData = ['debit' => $currentDebit + $difference];

                    if ($academicYearId) {
                        DB::connection('tenant')->table('particular_student')
                            ->where('particular_id', $particularId)
                            ->where('student_id', $studentId)
                            ->where('academic_year_id', $academicYearId)
                            ->update($updateData);
                    } else {
                        $particular->students()->updateExistingPivot($studentId, $updateData);
                    }
                } else {
                    $voucher->debit = 0;
                    $voucher->credit = abs($difference);
                    $voucher->notes = "Sales amount decreased from {$oldSales} to {$newSales} for {$particular->name}";

                    $currentCredit = $currentAssignment->pivot->credit ?? 0;
                    $updateData = ['credit' => $currentCredit + abs($difference)];

                    if ($academicYearId) {
                        DB::connection('tenant')->table('particular_student')
                            ->where('particular_id', $particularId)
                            ->where('student_id', $studentId)
                            ->where('academic_year_id', $academicYearId)
                            ->update($updateData);
                    } else {
                        $particular->students()->updateExistingPivot($studentId, $updateData);
                    }
                }

                $voucher->save();
            }

            DB::commit();
            return response()->json(['message' => 'Assignment updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteAssignment(Request $request, $particularId, $studentId)
    {
        $particular = Particular::findOrFail($particularId);
        $academicYearId = $request->get('academic_year_id');

        if ($academicYearId) {
            // Delete only the assignment for specific academic year
            DB::connection('tenant')->table('particular_student')
                ->where('particular_id', $particularId)
                ->where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->delete();
        } else {
            // Delete all assignments for this student and particular
            $particular->students()->detach($studentId);
        }

        return response()->json(['message' => 'Assignment deleted successfully']);
    }
}
