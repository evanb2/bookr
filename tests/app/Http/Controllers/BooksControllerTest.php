<?php

namespace Tests\App\Http\Controllers;

use TestCase;

class BooksControllerTest extends TestCase
{
    public function testGetBooks()
    {
        $this->get('/books')->seeStatusCode(200);
    }

    /** @test */
    public function indexShouldReturnACollectionOfRecords()
    {
        $this->get('/books')
             ->seeJson([
                 'title' => 'War of the Worlds'
             ])
            ->seeJson([
                'title' => 'A Wrinkle in Time'
            ]);
    }
}