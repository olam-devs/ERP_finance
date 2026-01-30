<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderBy('is_cash_book', 'desc')->orderBy('name')->get();
        return response()->json($books);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'is_cash_book' => 'boolean',
        ]);

        $book = Book::create($validated);
        return response()->json($book, 201);
    }

    public function show($id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $book->update($validated);
        return response()->json($book);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        if ($book->vouchers()->count() > 0) {
            return response()->json(['error' => 'Cannot delete book with existing vouchers'], 400);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted successfully']);
    }

    public function createCashBook(Request $request)
    {
        $cashBook = Book::firstOrCreate(
            ['is_cash_book' => true],
            [
                'name' => 'Cash Book',
                'opening_balance' => 0,
                'is_active' => true,
            ]
        );

        return response()->json($cashBook);
    }
}
