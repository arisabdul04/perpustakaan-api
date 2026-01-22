<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $data = $request->has('paginate')
            ? $query->paginate($request->input('paginate'))
            : $query->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|integer|exists:categories,id',
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'stock' => 'required|integer|min:0',
                'published_at' => 'required|date',
            ]);

            $book = Book::create($validated);

            return response()->json([
                'message' => "Successfully created book",
                'data' => $book
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create book'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'sometimes|integer|exists:categories,id',
                'title' => 'sometimes|string|max:255',
                'author' => 'sometimes|string|max:255',
                'stock' => 'sometimes|integer|min:0',
                'published_at' => 'sometimes|date',
            ]);

            $book = Book::findOrFail($id);
            $book->update($validated);

            return response()->json([
                'message' => "Successfully updated book",
                'data' => $book
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update book'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $book = Book::with('loan')->find($id);
            if (!$book) {
                return response()->json(['message' => 'Book not found'], 404);
            }
            
            if ($book->loan()->exists()) {
                return response()->json(['message' => 'Cannot delete book with active loans'], 422);
            }

            $book->delete();

            return response()->json(['message' => 'Successfully deleted']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Book not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete book'], 500);
        }
    }
}
