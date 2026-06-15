<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    // Fix: Disable Laravel's default created_at/updated_at handling 
    // since the database table handles it using the 'uploaded_at' column automatically.
    public $timestamps = false;

    protected $fillable = [
        'leave_request_id',
        'file_path',
        'file_name',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}