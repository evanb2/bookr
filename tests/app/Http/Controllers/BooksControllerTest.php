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

        $expected = [
            'data' => $books->toArray()
        ];

        $this->seeJsonEquals($expected);
    }

    public function testShowBooksReturnsValidBook()
    {
        $book = factory('App\Book')->create();
        $expected = [
            'data' => $book->toArray()
        ];

        //must use double quotes
        $this->get("/books/{$book->id}")->seeStatusCode(200)->seeJsonEquals($expected);
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

        $body = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals('The Invisible Man', $data['title']);
        $this->assertEquals('An invisible man is trapped in the terror of his own creation.', $data['description']);
        $this->assertEquals('H. G. Wells', $data['author']);
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer, but did not see one.');
        $this->seeInDatabase('books', ['title' => 'The Invisible Man']);
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

        $this->notSeeInDatabase('books', [
            'title'       => 'The War of the Worlds',
            'description' => 'A science ficiton masterpiece about Martians invading London',
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

        $body = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('data', $body);
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