<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{   
    protected $fillable = [
        'division_name',
        'code'
    ];
    
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    //
}
