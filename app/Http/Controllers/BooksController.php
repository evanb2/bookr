<?php


namespace App\Http\Controllers;


use App\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class BooksController
{
    public function index()
    {
        return Book::all();
    }

    public function show($id)
    {
        try {
            return Book::findorFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ]
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $book = Book::create($request->all());

        return response()->json(['created' => TRUE], 201, [
            'Location' => route('books.show', ['id' => $book->id])
        ]);
    }
}