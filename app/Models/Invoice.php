<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'number',
        'recipient',
        'line_items',
        'total',
        'notes',
        'sender_snapshot',
        'generated_at',
    ];

    protected $casts = [
        'recipient' => 'array',
        'line_items' => 'array',
        'sender_snapshot' => 'array',
        'total' => 'decimal:2',
        'generated_at' => 'datetime',
    ];
}
