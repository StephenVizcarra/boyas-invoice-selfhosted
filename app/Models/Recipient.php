<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    // Required for string primary keys — without these, Eloquent casts the PK to int
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'company',
        'address',
        'city_state_zip',
        'email',
    ];
}
