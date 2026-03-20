<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    protected $table = 'invoice_counter';

    // Table has no created_at column — only updated_at
    public $timestamps = false;

    protected $fillable = ['counter'];
}
