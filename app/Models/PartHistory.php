<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartHistory extends Model
{
    protected $fillable = [
        'part_id','user_id','action','before','after','changed_fields','note',
    ];

    protected $casts = [
        'before'         => 'array',
        'after'          => 'array',
        'changed_fields' => 'array',
    ];

    public function part(){ return $this->belongsTo(Part::class); }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id'); // แก้ namespace ให้ตรงโปรเจกต์คุณถ้าไม่ใช่ค่าเริ่มต้น
    }



}
