<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    // pinjam buku
    public function borrow(Request $request) {
        $request->validate(['book_id' => 'required|exists:books,id']);

        try {
            return DB::transaction(function () use ($request) {
                $book = Book::lockForUpdate()->findOrFail($request->book_id);

                if ($book->stock < 1) {
                    return response()->json(['message' => 'Stock habis'], 400);
                }

                $book->decrement('stock');

                Loan::create([
                    'user_id'   => Auth::id(),
                    'book_id'   => $book->id,
                    'loan_date' => now(),
                    'status'    => 'borrowed'
                ]);

                return response()->json(['message' => 'Book borrowed successfully']);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to borrow book'], 500);
        }
    }

    // kembalikan buku
    public function returnBook($id) {
        try {
            $loan = Loan::with('book')->findOrFail($id);

            return DB::transaction(function () use ($loan) {
                $loan->update([
                    'status'      => 'returned',
                    'return_date' => now()
                ]);

                if ($loan->book) {
                    $loan->book->increment('stock');
                }

                return response()->json(['message' => 'Buku dikembalikan']);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to return book'], 500);
        }
    }

    // daftar buku yang dipinjam user
    public function myLoans(Request $request) {
        try {
            $status = $request->status;

            $query = Loan::with('book')
                ->where('user_id', Auth::id())
                ->where(function ($q) use ($status) {
                    if (!empty($status))
                        $q->where('status', $status);

                    return $q;
                });

            if (request()->has('paginate')) {
                return $query->paginate($request->input('paginate'));
            }

            return $query->get();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to fetch loans'], 500);
        }
    }
}
