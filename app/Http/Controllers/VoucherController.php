<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Student;
use App\Models\Particular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::with(['student', 'particular', 'book']);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('particular_id')) {
            $query->where('particular_id', $request->particular_id);
        }

        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Paginate with 15 items per page
        $vouchers = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform vouchers to include display names for expense/suspense entries
        $vouchers->getCollection()->transform(function ($voucher) {
            // Add display_student_name - use payment_by_receipt_to as fallback
            $voucher->display_student_name = $voucher->student->name ?? $voucher->payment_by_receipt_to ?? 'N/A';

            // Add display_particular_name - determine based on voucher type and context
            if ($voucher->particular) {
                $voucher->display_particular_name = $voucher->particular->name;
            } elseif ($voucher->payment_by_receipt_to === 'Suspense Account') {
                $voucher->display_particular_name = 'Suspense Account';
            } elseif ($voucher->payment_by_receipt_to === 'Suspense Reversal') {
                $voucher->display_particular_name = 'Suspense Reversal';
            } elseif ($voucher->payment_by_receipt_to === 'Suspense Resolution') {
                $voucher->display_particular_name = 'Suspense Resolution';
            } elseif ($voucher->voucher_type === 'Payment' && !$voucher->student_id) {
                $voucher->display_particular_name = 'Expense';
            } else {
                $voucher->display_particular_name = 'N/A';
            }

            return $voucher;
        });

        return response()->json($vouchers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'student_id' => 'required|exists:students,id',
            'particular_id' => 'required|exists:particulars,id',
            'book_id' => 'nullable|exists:books,id',
            'voucher_type' => 'required|in:Sales,Receipt,Payment',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'payment_by_receipt_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $voucher = Voucher::create($validated);

            // Update particular_student pivot table
            $student = Student::findOrFail($validated['student_id']);
            $particular = Particular::findOrFail($validated['particular_id']);

            $pivot = $student->particulars()
                ->where('particular_id', $particular->id)
                ->first();

            if ($pivot) {
                $newDebit = $pivot->pivot->debit + ($validated['debit'] ?? 0);
                $newCredit = $pivot->pivot->credit + ($validated['credit'] ?? 0);

                $student->particulars()->updateExistingPivot($particular->id, [
                    'debit' => $newDebit,
                    'credit' => $newCredit,
                ]);
            }

            DB::commit();
            return response()->json($voucher, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $voucher = Voucher::with(['student', 'particular', 'book'])->findOrFail($id);
        return response()->json($voucher);
    }

    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'payment_by_receipt_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $voucher->update($validated);
        return response()->json($voucher);
    }

    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);

        DB::beginTransaction();
        try {
            // Reverse the pivot table changes
            $student = $voucher->student;
            $particular = $voucher->particular;

            $pivot = $student->particulars()
                ->where('particular_id', $particular->id)
                ->first();

            if ($pivot) {
                $newDebit = max(0, $pivot->pivot->debit - $voucher->debit);
                $newCredit = max(0, $pivot->pivot->credit - $voucher->credit);

                $student->particulars()->updateExistingPivot($particular->id, [
                    'debit' => $newDebit,
                    'credit' => $newCredit,
                ]);
            }

            $voucher->delete();

            DB::commit();
            return response()->json(['message' => 'Voucher deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchStudent(Request $request)
    {
        $search = $request->get('q', '');

        $students = Student::where('name', 'LIKE', "%{$search}%")
            ->orWhere('student_reg_no', 'LIKE', "%{$search}%")
            ->with('schoolClass')
            ->limit(10)
            ->get();

        return response()->json($students);
    }
}
