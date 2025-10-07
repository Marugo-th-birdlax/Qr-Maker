<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
class Part extends Model
{
    use SoftDeletes; 
    
    protected $fillable = [
        'no','pic','type','part_no','part_name','supplier_name','supplier_code',
        'supplier','location','moq','qty_per_box','remark','item_no','unit','date',
        'qr_payload','is_active','deactivated_at','updated_by',
    ];


    protected $casts = [
    'date'        => 'datetime',
    'qty_per_box' => 'integer',
    'moq'        => 'integer',
    'is_active' => 'boolean',
    'deactivated_at' => 'datetime',
    ];
    public function histories(){ return $this->hasMany(PartHistory::class); }

    // scope สำหรับคิวรีเฉพาะที่เปิดใช้งาน
    public function scopeActive($q){ return $q->where('is_active', true); }

    public function scopeInactive($q){ return $q->where('is_active', false); }
}
