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
        'is_half_day', // Added to allow mass assignment
    ];

    // Ensures Laravel treats this strictly as true/false
    protected $casts = [
        'is_half_day' => 'boolean',
    ];
}