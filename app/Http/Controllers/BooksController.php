<?php


namespace App\Http\Controllers;


use App\Book;
use App\Transformer\BookTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    /**
     * @return array
     */
    public function index()
    {
        return $this->collection(Book::all(), new BookTransformer());
    }

    /**
     * @param $id
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        try {
            return $this->item(Book::findOrFail($id), new BookTransformer());
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ]
            ], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $book = Book::create($request->all());
        $data = $this->item($book, new BookTransformer());

        return response()->json($data, 201, [
            'Location' => route('books.show', ['id' => $book->id])
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ]
            ], 404);
        }

        $book->fill($request->all());
        $book->save();

        return $this->item($book, new BookTransformer());
    }

    /**
     * @param $id
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy($id)
    {
        try {
            $book = Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ]
            ], 404);
        }

        $book->delete();

        return response(NULL, 204);
    }
}