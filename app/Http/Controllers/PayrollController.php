<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\PayrollEntry;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // Staff Management
    public function indexStaff()
    {
        $staff = Staff::orderBy('name')->get();
        return response()->json(['staff' => $staff]);
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'staff_id' => 'required|string|unique:staff',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'date_joined' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        $staff = Staff::create($validated);

        return response()->json($staff, 201);
    }

    public function showStaff($id)
    {
        $staff = Staff::with('payrollEntries')->findOrFail($id);
        return response()->json($staff);
    }

    public function updateStaff(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'staff_id' => 'required|string|unique:staff,staff_id,' . $id,
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'date_joined' => 'nullable|date',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $staff->update($validated);

        return response()->json($staff);
    }

    public function destroyStaff($id)
    {
        $staff = Staff::findOrFail($id);

        if ($staff->payrollEntries()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete staff with existing payroll entries'
            ], 400);
        }

        $staff->delete();

        return response()->json([
            'message' => 'Staff deleted successfully'
        ]);
    }

    public function staffPaymentHistory($id)
    {
        $staff = Staff::findOrFail($id);
        $payments = $staff->payrollEntries()
            ->with('book')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'staff' => $staff,
            'payments' => $payments,
        ]);
    }

    // Payroll Entry Management
    public function indexPayroll(Request $request)
    {
        $query = PayrollEntry::with(['staff', 'book']);

        if ($request->has('month')) {
            $query->where('month', $request->month);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        $payrolls = $query->orderBy('payment_date', 'desc')->get();

        return response()->json($payrolls);
    }

    public function storePayroll(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'payment_date' => 'required|date',
            'book_id' => 'required|exists:books,id',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $payroll = PayrollEntry::create($validated);

        return response()->json($payroll, 201);
    }

    public function showPayroll($id)
    {
        $payroll = PayrollEntry::with(['staff', 'book'])->findOrFail($id);
        return response()->json($payroll);
    }

    public function updatePayroll(Request $request, $id)
    {
        $payroll = PayrollEntry::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payroll->update($validated);

        return response()->json($payroll);
    }

    public function destroyPayroll($id)
    {
        $payroll = PayrollEntry::findOrFail($id);
        $payroll->delete();

        return response()->json([
            'message' => 'Payroll entry deleted successfully'
        ]);
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $payrolls = PayrollEntry::with('staff')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $totalAmount = $payrolls->sum('amount');

        return response()->json([
            'month' => $month,
            'year' => $year,
            'payrolls' => $payrolls,
            'total_amount' => $totalAmount,
            'staff_count' => $payrolls->count(),
        ]);
    }

    // CSV Upload/Download
    public function downloadStaffTemplate()
    {
        $filename = "staff-import-template.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['staff_id', 'name', 'position', 'department', 'monthly_salary', 'phone', 'email', 'bank_name', 'bank_account', 'date_joined']);
        fputcsv($handle, ['STF001', 'John Doe', 'Teacher', 'Science', '800000', '255712345678', 'john@example.com', 'NMB Bank', '1234567890', '2024-01-01']);

        fclose($handle);
        exit;
    }

    public function uploadStaffCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($data);

        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                $staffData = array_combine($headers, $row);

                Staff::updateOrCreate(
                    ['staff_id' => $staffData['staff_id']],
                    [
                        'name' => $staffData['name'],
                        'position' => $staffData['position'],
                        'department' => $staffData['department'] ?? null,
                        'monthly_salary' => $staffData['monthly_salary'],
                        'phone' => $staffData['phone'] ?? null,
                        'email' => $staffData['email'] ?? null,
                        'bank_name' => $staffData['bank_name'] ?? null,
                        'bank_account' => $staffData['bank_account'] ?? null,
                        'date_joined' => $staffData['date_joined'] ?? null,
                        'status' => 'active',
                        'created_by' => auth()->id(),
                    ]
                );

                $imported++;
            }

            DB::commit();
            return response()->json([
                'message' => "Successfully imported {$imported} staff members",
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
