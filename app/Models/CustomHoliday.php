<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'is_half_day',
        'is_regular',
        'is_active',
    ];

    protected $casts = [
        'is_half_day' => 'boolean',
        'is_regular'  => 'boolean',
        'is_active'   => 'boolean',
        'date'        => 'date',
    ];
}