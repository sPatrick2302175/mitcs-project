<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    const UPDATED_AT = null;

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