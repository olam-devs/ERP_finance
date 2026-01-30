<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Book;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['book', 'processor'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('book_id') && $request->book_id != '') {
            $query->where('book_id', $request->book_id);
        }

        if ($request->has('from_date') && $request->from_date != '') {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date != '') {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $expenses = $query->paginate($perPage);

        // Calculate summary statistics (from all expenses, not just current page)
        $allExpenses = Expense::all();
        $totalPending = $allExpenses->where('status', 'pending')->sum('amount');
        $totalProcessed = $allExpenses->where('status', 'processed')->sum('amount');
        $totalCount = $allExpenses->count();
        $pendingCount = $allExpenses->where('status', 'pending')->count();
        $processedCount = $allExpenses->where('status', 'processed')->count();

        return response()->json([
            'expenses' => $expenses,
            'summary' => [
                'total_pending' => $totalPending,
                'total_processed' => $totalProcessed,
                'total_count' => $totalCount,
                'pending_count' => $pendingCount,
                'processed_count' => $processedCount,
                'total_amount' => $totalPending + $totalProcessed,
            ],
        ]);
    }

    public function summary(Request $request)
    {
        $query = Expense::query();

        // Apply date filters if provided
        if ($request->has('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $pendingExpenses = (clone $query)->where('status', 'pending')->get();
        $processedExpenses = (clone $query)->where('status', 'processed')->get();
        $allExpenses = $query->get();

        return response()->json([
            'pending_count' => $pendingExpenses->count(),
            'pending_amount' => $pendingExpenses->sum('amount'),
            'processed_count' => $processedExpenses->count(),
            'processed_amount' => $processedExpenses->sum('amount'),
            'total_count' => $allExpenses->count(),
            'total_amount' => $allExpenses->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_name' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'book_id' => 'nullable|exists:books,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    public function show($id)
    {
        $expense = Expense::with(['book', 'voucher', 'processor'])->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'error' => 'Cannot update processed expense'
            ], 400);
        }

        $validated = $request->validate([
            'expense_name' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'book_id' => 'required|exists:books,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'error' => 'Cannot delete processed expense'
            ], 400);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }

    public function process(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'error' => 'Expense already processed'
            ], 400);
        }

        // Check if the book has sufficient balance in cash view
        // In cash view: DR = credits (receipts), CR = debits (payments)
        // So we check if sum of credits (cash view DR) > 0
        $book = Book::findOrFail($expense->book_id);
        $cashViewDR = $book->vouchers()->sum('credit'); // Total receipts in cash view
        $cashViewCR = $book->vouchers()->sum('debit');  // Total payments in cash view
        $cashViewBalance = $book->opening_balance + $cashViewDR - $cashViewCR;
        
        if ($cashViewBalance <= 0) {
            return response()->json([
                'error' => 'Insufficient balance in cash view. Current cash balance: TSh ' . number_format($cashViewBalance, 2)
            ], 400);
        }

        if ($cashViewBalance < $expense->amount) {
            return response()->json([
                'error' => 'Insufficient cash balance to process this expense. Current cash balance: TSh ' . number_format($cashViewBalance, 2) . ', Required: TSh ' . number_format($expense->amount, 2)
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create a voucher for this expense - money going out
            // In Bank view: DR = amount (debit shows expense going out)
            // In Cash view: CR = amount (cash view reverses, so debit becomes credit)
            // The expense name will appear in the "Student Name" column
            // and "Expense" will appear in the "Particular" column
            $voucher = Voucher::create([
                'date' => $expense->transaction_date,
                'student_id' => null, // No student association for expenses
                'particular_id' => null, // No particular association for expenses
                'book_id' => $expense->book_id,
                'voucher_type' => 'Payment',
                'debit' => $expense->amount, // DR in bank view, CR in cash view
                'credit' => 0,
                'payment_by_receipt_to' => $expense->expense_name, // This shows in the "Student Name" column
                'notes' => $expense->description,
                'created_by' => auth()->id(),
            ]);

            $expense->update([
                'status' => 'processed',
                'voucher_id' => $voucher->id,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Expense processed successfully',
                'expense' => $expense->fresh(),
                'voucher' => $voucher,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status === 'pending') {
            return response()->json([
                'error' => 'Expense is not processed yet'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Delete the voucher
            if ($expense->voucher) {
                $expense->voucher->delete();
            }

            $expense->update([
                'status' => 'cancelled',
                'voucher_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Expense cancelled successfully',
                'expense' => $expense->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function analytics(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Default to current month if no dates provided
        if (!$fromDate || !$toDate) {
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->endOfMonth()->toDateString();
        }

        // Get processed expenses within the date range
        $expenses = Expense::where('status', 'processed')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->with('book')
            ->orderBy('transaction_date', 'asc')
            ->get();

        // Determine if we're looking at a month or year range
        $daysDiff = (strtotime($toDate) - strtotime($fromDate)) / (60 * 60 * 24);
        $isMonthly = $daysDiff <= 31;

        // Generate expense trend data
        $expenseTrend = [];
        if ($isMonthly) {
            // Daily data for month view
            $currentDate = new \DateTime($fromDate);
            $endDate = new \DateTime($toDate);

            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dailyTotal = $expenses->filter(function($expense) use ($dateStr) {
                    return $expense->transaction_date === $dateStr;
                })->sum('amount');

                $expenseTrend[] = [
                    'label' => $currentDate->format('M d'),
                    'amount' => (float) $dailyTotal
                ];

                $currentDate->modify('+1 day');
            }
        } else {
            // Monthly data for year view
            $months = [];
            $currentDate = new \DateTime($fromDate);
            $endDate = new \DateTime($toDate);

            while ($currentDate <= $endDate) {
                $monthKey = $currentDate->format('Y-m');
                if (!isset($months[$monthKey])) {
                    $months[$monthKey] = [
                        'label' => $currentDate->format('M Y'),
                        'amount' => 0
                    ];
                }
                $currentDate->modify('+1 month');
            }

            foreach ($expenses as $expense) {
                $monthKey = date('Y-m', strtotime($expense->transaction_date));
                if (isset($months[$monthKey])) {
                    $months[$monthKey]['amount'] += (float) $expense->amount;
                }
            }

            $expenseTrend = array_values($months);
        }

        // Generate books distribution data (only processed expenses)
        $booksDistribution = [];
        $expensesByBook = $expenses->groupBy('book_id');

        foreach ($expensesByBook as $bookId => $bookExpenses) {
            $book = $bookExpenses->first()->book;
            if ($book) {
                $booksDistribution[] = [
                    'name' => $book->name,
                    'amount' => (float) $bookExpenses->sum('amount')
                ];
            }
        }

        return response()->json([
            'expense_trend' => $expenseTrend,
            'books_distribution' => $booksDistribution
        ]);
    }
}
