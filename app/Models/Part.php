<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'no','part_no','part_name','supplier_name','supplier_code','moq','date','qr_payload'
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
