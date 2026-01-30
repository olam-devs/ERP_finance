<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookTransaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookTransactionController extends Controller
{
    /**
     * Get all transactions for a book
     */
    public function index(Request $request, $bookId)
    {
        $book = Book::findOrFail($bookId);

        $query = BookTransaction::where('book_id', $bookId)
            ->with(['creator', 'voucher'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('type') && in_array($request->type, ['deposit', 'withdrawal'])) {
            $query->where('transaction_type', $request->type);
        }

        $transactions = $query->paginate(15);

        // Calculate totals
        $totalDeposits = BookTransaction::where('book_id', $bookId)
            ->where('transaction_type', 'deposit')
            ->sum('amount');

        $totalWithdrawals = BookTransaction::where('book_id', $bookId)
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');

        return response()->json([
            'book' => $book,
            'transactions' => $transactions,
            'summary' => [
                'total_deposits' => $totalDeposits,
                'total_withdrawals' => $totalWithdrawals,
                'net_amount' => $totalDeposits - $totalWithdrawals,
            ]
        ]);
    }

    /**
     * Store a new deposit
     */
    public function storeDeposit(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'short_notes' => 'nullable|string|max:255',
            'full_details' => 'nullable|string',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        DB::beginTransaction();
        try {
            // Create voucher entry for deposit
            // Deposit: CR in bank view, DR in cash view
            // So we use credit = amount, debit = 0
            $voucher = Voucher::create([
                'date' => $validated['transaction_date'],
                'student_id' => null,
                'particular_id' => null,
                'book_id' => $validated['book_id'],
                'voucher_type' => 'Receipt',
                'debit' => 0,
                'credit' => $validated['amount'],
                'payment_by_receipt_to' => 'Bank Deposit',
                'notes' => $validated['short_notes'] ?? 'Bank Deposit',
                'created_by' => auth()->id(),
            ]);

            // Create book transaction record
            $transaction = BookTransaction::create([
                'book_id' => $validated['book_id'],
                'transaction_type' => 'deposit',
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'short_notes' => $validated['short_notes'] ?? null,
                'full_details' => $validated['full_details'] ?? null,
                'voucher_id' => $voucher->id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Deposit recorded successfully',
                'transaction' => $transaction->load(['book', 'voucher']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record deposit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a new withdrawal
     */
    public function storeWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'short_notes' => 'nullable|string|max:255',
            'full_details' => 'nullable|string',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        // Check if there's sufficient balance using CASH VIEW balance
        // In cash view: receipts (credits) are DR and increase balance
        // Withdrawals take money out, so we need cash view balance > withdrawal amount
        $cashViewBalance = $book->getCashViewBalance();
        if ($cashViewBalance <= 0) {
            return response()->json([
                'error' => 'Insufficient balance for withdrawal. Cash view balance must be greater than zero. Current cash view balance: TSh ' . number_format($cashViewBalance, 2)
            ], 400);
        }
        if ($cashViewBalance < $validated['amount']) {
            return response()->json([
                'error' => 'Insufficient balance for withdrawal. Requested: TSh ' . number_format($validated['amount'], 2) . ', Available (cash view): TSh ' . number_format($cashViewBalance, 2)
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create voucher entry for withdrawal
            // Withdrawal: DR in bank view, CR in cash view
            // So we use debit = amount, credit = 0
            $voucher = Voucher::create([
                'date' => $validated['transaction_date'],
                'student_id' => null,
                'particular_id' => null,
                'book_id' => $validated['book_id'],
                'voucher_type' => 'Payment',
                'debit' => $validated['amount'],
                'credit' => 0,
                'payment_by_receipt_to' => 'Bank Withdrawal',
                'notes' => $validated['short_notes'] ?? 'Bank Withdrawal',
                'created_by' => auth()->id(),
            ]);

            // Create book transaction record
            $transaction = BookTransaction::create([
                'book_id' => $validated['book_id'],
                'transaction_type' => 'withdrawal',
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'short_notes' => $validated['short_notes'] ?? null,
                'full_details' => $validated['full_details'] ?? null,
                'voucher_id' => $voucher->id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Withdrawal recorded successfully',
                'transaction' => $transaction->load(['book', 'voucher']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record withdrawal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get a single transaction
     */
    public function show($id)
    {
        $transaction = BookTransaction::with(['book', 'voucher', 'creator'])->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * Delete a transaction (and its associated voucher)
     */
    public function destroy($id)
    {
        $transaction = BookTransaction::findOrFail($id);

        DB::beginTransaction();
        try {
            // Delete associated voucher if exists
            if ($transaction->voucher_id) {
                Voucher::destroy($transaction->voucher_id);
            }

            $transaction->delete();

            DB::commit();

            return response()->json(['message' => 'Transaction deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete transaction: ' . $e->getMessage()], 500);
        }
    }
}
