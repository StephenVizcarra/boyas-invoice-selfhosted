<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    protected $table = 'invoice_counter';

    public $timestamps = false;

    protected $fillable = ['counter'];
}
