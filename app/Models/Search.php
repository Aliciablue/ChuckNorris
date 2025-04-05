<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Search extends Model
{
    use HasFactory;
    protected $fillable = [
        'query',
        'type',
        'results',
        'email',
    ];
}
