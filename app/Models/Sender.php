<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    protected $table = 'sender';

    protected $fillable = [
        'name',
        'company',
        'address',
        'city_state_zip',
        'email',
        'phone',
        'logo_path',
    ];
}
