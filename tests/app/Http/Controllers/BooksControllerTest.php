<?php

namespace Tests\App\Http\Controllers;

use Laravel\Lumen\Testing\DatabaseMigrations;
use TestCase;

class BooksControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetBooks()
    {
        $this->get('/books')->seeStatusCode(200);
    }

    public function testIndexShouldReturnACollectionOfRecords()
    {
        $books = factory('App\Book', 2)->create();

        $this->get('/books');

        foreach ($books as $book) {
            $this->seeJson(['title' => $book->title]);
        }
    }

    public function testShowBooksReturnsValidBook()
    {
        $book = factory('App\Book')->create();

        //must use double quotes
        $this->get("/books/{$book->id}")
             ->seeStatusCode(200)
             ->seeJson([
                 'id'          => $book->id,
                 'title'       => $book->title,
                 'description' => $book->description,
                 'author'      => $book->author
             ]);

        $data = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    public function testShowBooksFailsWithoutBookId()
    {
        $this->get('/books/99999', ['Accept' => 'application/json'])
             ->seeStatusCode(404)
             ->seeJson([
                 'error' => [
                     'message' => 'Book not found'
                 ]
             ]);
    }

    public function testShowRouteShouldNotMatchAnInvalidRoute()
    {
        $this->get('/books/this-is-invalid');

        $this->assertNotRegExp('/Book not found/', $this->response->getContent(), 'BooksController@show route matching when it should not.');
    }

    public function testStoreShouldSaveNewBook()
    {
        $this->post('/books', [
            'title'       => 'The Invisible Man',
            'description' => 'An invisible man is trapped in the terror of his own creation.',
            'author'      => 'H. G. Wells'
        ]);

        $this->seeJson(['created' => TRUE])
            ->seeInDatabase('books', ['title' => 'The Invisible Man']);
    }

    public function testStoreShouldRespondWith201AndLocationHeaderOnSuccess()
    {
        $this->post('/books', [
            'title'       => 'The Invisible Man',
            'description' => 'An invisible man is trapped in the terror of his own creation.',
            'author'      => 'H. G. Wells'
        ]);

        $this->seeStatusCode(201)->seeHeaderWithRegExp('Location', '#/books/[\d]+$#');
    }

    public function testUpdateShouldOnlyChangeFillableFields()
    {
        $book = factory('App\Book')->create([
            'title'       => 'War of the Worlds',
            'description' => 'A science fiction masterpiece about Martians invading London',
            'author'      => 'H. G. Wells'
        ]);

        $this->put("/books/{$book->id}", [
            'id'          => 5,
            'title'       => 'The War of The Worlds',
            'description' => 'The book is way better than the movie.',
            'author'      => 'Wells, H.G.'
        ]);

        $this->seeStatusCode(200)
            ->seeJson([
                'id'          => 1,
                'title'       => 'The War of The Worlds',
                'description' => 'The book is way better than the movie.',
                'author'      => 'Wells, H.G.'
            ])
            ->seeInDatabase('books', [
                'title' => 'The War of The Worlds'
            ]);
    }

    public function testUpdateShouldFailWithAnInvalidId()
    {
        $this->put('/books/99999999999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not found'
                ]
            ]);
    }

    public function testUpdateShouldNotMatchAnInvalidRoute()
    {
        $this->put('/books/this-is-invalid')->seeStatusCode(404);
    }

    public function testDestroyShouldRemoveAValidBook()
    {
        $book = factory('App\Book')->create();
        $this->delete("/books/{$book->id}")
            ->seeStatusCode(204)
            ->isEmpty();

        $this->notSeeInDatabase('books', ['id' => $book->id]);
    }

    public function testDestroyShouldReturn404WithInvalidId()
    {
        $this->delete('/books/99999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not found'
                ]
            ]);
    }

    public function testDestroyShouldNotMatchAnInvalidRoute()
    {
        $this->delete('/books/this-is-invalid')->seeStatusCode(404);
    }
}