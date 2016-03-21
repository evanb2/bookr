<?php


namespace App\Http\Controllers;


class BooksController
{
    public function index()
    {
        return [
            ['title' => 'War of the Worlds'],
            ['title' => 'A Wrinkle in Time']
        ];
    }
}