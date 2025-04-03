<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
        protected $fillable = [
            'query',
            'type',
            'results',
            'email',
    ];
}
