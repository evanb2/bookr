<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    //define which fields are mass-assignable
    protected $fillable = [
        'title',
        'description',
        'author'
    ];
}