<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application for Leave</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        table { border-collapse: collapse; margin-bottom: 15px; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .bg-gray { background-color: #f0f0f0; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div>Civil Service Form No. 6</div>
        <div class="title">APPLICATION FOR LEAVE</div>
    </div>

    <table>
        <tr>
            <td colspan="4" class="bg-gray text-center">1. DETAILS OF APPLICANT</td>
        </tr>
        <tr>
            <td width="25%"><strong>Name:</strong></td>
            <td width="75%" colspan="3">{{ $leaveRequest->employee->last_name }}, {{ $leaveRequest->employee->first_name }}</td>
        </tr>
        <tr>
            <td><strong>Date of Filing:</strong></td>
            <td>{{ \Carbon\Carbon::parse($leaveRequest->date_of_filing)->format('F d, Y') }}</td>
            <td width="25%"><strong>Position:</strong></td>
            <td width="25%">{{ $leaveRequest->employee->position ?? 'N/A' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2" class="bg-gray text-center">6. DETAILS OF APPLICATION</td>
        </tr>
        <tr>
            <td width="50%">
                <strong>6.A TYPE OF LEAVE:</strong><br>
                {{ $leaveRequest->leave_type }}
                @if($leaveRequest->leave_type === 'Others')
                    ({{ $leaveRequest->leave_type_others }})
                @endif
            </td>
            <td width="50%">
                <strong>6.B DETAILS OF LEAVE:</strong><br>
                {{ $leaveRequest->leave_detail_category ?? 'N/A' }} <br>
                Specifics: {{ $leaveRequest->leave_detail_specifics ?? 'None' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>6.C NUMBER OF WORKING DAYS APPLIED FOR:</strong><br>
                {{ $leaveRequest->working_days_applied }} days<br><br>
                <strong>INCLUSIVE DATES:</strong><br>
                {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}
            </td>
            <td>
                <strong>6.D COMMUTATION:</strong><br>
                {{ $leaveRequest->commutation_requested ? 'Requested' : 'Not Requested' }}
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2" class="bg-gray text-center">7. DETAILS OF ACTION ON APPLICATION</td>
        </tr>
        <tr>
            <td width="50%">
                <strong>7.A CERTIFICATION OF LEAVE CREDITS:</strong><br>
                Vacation Leave: {{ $leaveRequest->employee->vacation_leave_balance }}<br>
                Sick Leave: {{ $leaveRequest->employee->sick_leave_balance }}
            </td>
            <td width="50%">
                <strong>7.B RECOMMENDATION:</strong><br>
                {{ $leaveRequest->recommendation_reason ?? 'None provided' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>7.C APPROVED FOR:</strong><br>
                Days with Pay: {{ $leaveRequest->days_with_pay ?? 0 }}<br>
                Days without Pay: {{ $leaveRequest->days_without_pay ?? 0 }}<br><br>
                
                <strong>7.D DISAPPROVED DUE TO:</strong><br>
                {{ $leaveRequest->disapproval_reason ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-center" style="padding-top: 20px; font-weight:bold; font-size: 14px;">
                STATUS: {{ strtoupper($leaveRequest->status) }}
            </td>
        </tr>
    </table>

</body>
</html>