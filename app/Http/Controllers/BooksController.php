<?php


namespace App\Http\Controllers;


use App\Book;

class BooksController
{
    public function index()
    {
        return Book::all();
    }
}