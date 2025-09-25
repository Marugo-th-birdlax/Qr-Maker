<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
    'no','part_no','part_name',
    'supplier_name','supplier_code','supplier',
    'pic','type','location',
    'qty_per_box','moq','remark','item_no','unit',
    'date','qr_payload',
    ];

    protected $casts = [
    'date'        => 'datetime',
    'qty_per_box' => 'integer',
    'moq'        => 'integer',
    ];

}
