<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{   
    protected $fillable = [
        'division_name',
        'code',
        'department_id'
    ];
    
    public function departments()
    {
        return $this->belongsTo(Department::class);
    }
    //
}
